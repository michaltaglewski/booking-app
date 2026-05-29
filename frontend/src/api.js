import { API_BASE_URL } from './config.js';

export async function fetchRooms() {
  const response = await fetch(`${API_BASE_URL}/api/rooms`);

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`);
  }

  return response.json();
}
