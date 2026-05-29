export function resolveRoute(pathname) {
  if (pathname === '/' || pathname === '/rooms') {
    return 'rooms';
  }

  return 'not-found';
}
