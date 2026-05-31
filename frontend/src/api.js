import { API_BASE_URL } from './config.js';

export class ApiError extends Error {
  constructor(message, status, body) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.body = body;
  }
}

async function readResponseBody(response) {
  const contentType = response.headers.get('content-type') ?? '';

  if (contentType.includes('application/json')) {
    try {
      return await response.json();
    } catch {
      return '';
    }
  }

  return response.text();
}

function createApiUrl(path) {
  return new URL(`${API_BASE_URL}${path}`, window.location.origin);
}

function readCookie(name) {
  const match = document.cookie
    .split('; ')
    .find((cookie) => cookie.startsWith(`${name}=`));

  if (!match) {
    return '';
  }

  return decodeURIComponent(match.slice(name.length + 1));
}

export async function ensureCsrfCookie(signal) {
  await fetch(createApiUrl('/sanctum/csrf-cookie'), {
    method: 'GET',
    signal,
    credentials: 'include',
    headers: {
      accept: 'application/json',
    },
  });
}

async function requestJson(path, { method = 'GET', body, signal } = {}) {
  const csrfToken = readCookie('XSRF-TOKEN');

  const response = await fetch(createApiUrl(path), {
    method,
    signal,
    credentials: 'include',
    headers: {
      accept: 'application/json',
      ...(body ? { 'content-type': 'application/json' } : {}),
      ...(csrfToken ? { 'x-xsrf-token': csrfToken } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  if (!response.ok) {
    const body = await readResponseBody(response);
    const message = typeof body === 'object' && body !== null && typeof body.message === 'string'
      ? body.message
      : `Request failed with status ${response.status}`;

    throw new ApiError(message, response.status, body);
  }

  return response.json();
}

export async function fetchRooms({ startsAt, endsAt, signal } = {}) {
  const url = createApiUrl('/api/rooms');

  if (startsAt) {
    url.searchParams.set('starts_at', startsAt);
  }

  if (endsAt) {
    url.searchParams.set('ends_at', endsAt);
  }

  const response = await fetch(url, {
    signal,
    credentials: 'include',
    headers: {
      accept: 'application/json',
    },
  });

  if (!response.ok) {
    const body = await readResponseBody(response);
    const message = typeof body === 'object' && body !== null && typeof body.message === 'string'
      ? body.message
      : `Request failed with status ${response.status}`;

    throw new ApiError(message, response.status, body);
  }

  return response.json();
}

export async function createBooking(
  { roomId, startsAt, endsAt, participantsCount, signal } = {},
) {
  const csrfToken = readCookie('XSRF-TOKEN');

  const response = await fetch(createApiUrl('/api/bookings'), {
    method: 'POST',
    signal,
    credentials: 'include',
    headers: {
      accept: 'application/json',
      'content-type': 'application/json',
      ...(csrfToken ? { 'x-xsrf-token': csrfToken } : {}),
    },
    body: JSON.stringify({
      room_id: roomId,
      starts_at: startsAt,
      ends_at: endsAt,
      participants_count: participantsCount,
    }),
  });

  if (!response.ok) {
    const body = await readResponseBody(response);
    const message = typeof body === 'object' && body !== null && typeof body.message === 'string'
      ? body.message
      : `Request failed with status ${response.status}`;

    throw new ApiError(message, response.status, body);
  }

  return readResponseBody(response);
}

export async function fetchCurrentUser(signal) {
  const response = await fetch(createApiUrl('/api/user'), {
    signal,
    credentials: 'include',
    headers: {
      accept: 'application/json',
    },
  });

  if (!response.ok) {
    const body = await readResponseBody(response);
    const message = typeof body === 'object' && body !== null && typeof body.message === 'string'
      ? body.message
      : `Request failed with status ${response.status}`;

    throw new ApiError(message, response.status, body);
  }

  const user = await response.json();

  if (!user) {
    throw new ApiError('Unauthenticated', 401, user);
  }

  return user;
}

export async function loginUser({ email, password, signal } = {}) {
  return requestJson('/api/login', {
    method: 'POST',
    signal,
    body: {
      email,
      password,
    },
  });
}

export async function registerUser({
  name,
  email,
  password,
  passwordConfirmation,
  signal,
} = {}) {
  return requestJson('/api/register', {
    method: 'POST',
    signal,
    body: {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    },
  });
}

export async function logoutUser(signal) {
  return requestJson('/api/logout', {
    method: 'POST',
    signal,
  });
}
