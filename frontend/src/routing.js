export const ROUTING = {
  defaultRoute: '/rooms',
  guestRedirectTo: '/login',
};

export const ROUTES = {
  rooms: {
    path: ROUTING.defaultRoute,
    aliases: ['/'],
  },
  reservations: {
    path: '/reservations',
    auth: {
      requiresUser: true,
    },
  },
  login: {
    path: ROUTING.guestRedirectTo,
    auth: {
      guestOnly: true,
    },
  },
  register: {
    path: '/register',
    auth: {
      guestOnly: true,
    },
  },
};

export function resolveRoute(pathname) {
  const entry = Object.entries(ROUTES).find(([, config]) => {
    if (config.path === pathname) {
      return true;
    }

    return Array.isArray(config.aliases) && config.aliases.includes(pathname);
  });

  return entry?.[0] ?? 'not-found';
}

export function getRouteConfig(routeName) {
  return ROUTES[routeName] ?? null;
}
