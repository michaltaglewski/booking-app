export function resolveRoute(pathname) {
  if (pathname === '/' || pathname === '/rooms') {
    return 'rooms';
  }

  if (pathname === '/reservations') {
    return 'reservations';
  }

  return 'not-found';
}
