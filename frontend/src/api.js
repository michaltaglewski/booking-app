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

export async function fetchRooms({ startsAt, endsAt, signal } = {}) {
  const url = new URL(`${API_BASE_URL}/api/rooms`, window.location.origin);

  if (startsAt) {
    url.searchParams.set('starts_at', startsAt);
  }

  if (endsAt) {
    url.searchParams.set('ends_at', endsAt);
  }

  const response = await fetch(url, {
    signal,
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
  const response = await fetch(new URL(`${API_BASE_URL}/api/bookings`, window.location.origin), {
    method: 'POST',
    signal,
    headers: {
      accept: 'application/json',
      'content-type': 'application/json',
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
