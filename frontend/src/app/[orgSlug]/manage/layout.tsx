'use client';

import { useEffect } from 'react';
import { useRouter, usePathname, useParams } from 'next/navigation';
import Link from 'next/link';
import { useAuthStore } from '@/store/authStore';
import { getToken } from '@/lib/auth';

interface NavItem {
  label: string;
  href: string;
  icon: React.ReactNode;
}

const orgNav = (orgSlug: string): NavItem[] => [
  {
    label: 'Dashboard',
    href: `/${orgSlug}/manage`,
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />,
  },
  {
    label: 'Members',
    href: `/${orgSlug}/manage/members`,
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />,
  },
  {
    label: 'Subscriptions',
    href: `/${orgSlug}/manage/subscriptions`,
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />,
  },
  {
    label: 'Orders',
    href: `/${orgSlug}/manage/orders`,
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />,
  },
  {
    label: 'Settings',
    href: `/${orgSlug}/manage/settings`,
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />,
  },
];

function NavLink({ item, exact = false }: { item: NavItem; exact?: boolean }) {
  const pathname = usePathname();
  const active = exact
    ? pathname === item.href
    : pathname === item.href || pathname.startsWith(item.href + '/');

  return (
    <Link
      href={item.href}
      className={`flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
        active
          ? 'bg-emerald-50 text-emerald-700'
          : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
      }`}
    >
      <svg className="w-4 h-4 shrink-0" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
        {item.icon}
      </svg>
      {item.label}
    </Link>
  );
}

export default function ManageLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const { orgSlug } = useParams<{ orgSlug: string }>();
  const { user, currentOrganisation, organisations, logout, loadUser, loadOrganisations } = useAuthStore();

  useEffect(() => {
    if (getToken() && !user) {
      loadUser().catch(() => {});
    }
  }, [user, loadUser]);

  useEffect(() => {
    if (user && organisations.length === 0) {
      loadOrganisations().catch(() => {});
    }
  }, [user, organisations.length, loadOrganisations]);

  const handleLogout = async () => {
    await logout();
    router.push(`/${orgSlug}/login`);
  };

  const org = currentOrganisation?.seo_name === orgSlug
    ? currentOrganisation
    : organisations.find((o) => o.seo_name === orgSlug);

  const nav = orgNav(orgSlug);

  return (
    <div className="min-h-screen bg-gray-50 flex">
      {/* Sidebar */}
      <aside className="w-60 shrink-0 bg-white border-r border-gray-200 flex flex-col sticky top-0 h-screen">
        {/* Logo */}
        <div className="px-5 py-4 border-b border-gray-200">
          <Link href="/" className="flex items-center gap-2 mb-3">
            <div className="w-7 h-7 bg-primary-600 rounded-md flex items-center justify-center">
              <span className="text-white text-xs font-bold">M</span>
            </div>
            <span className="text-base font-bold text-gray-900">Membix</span>
          </Link>
          {org && (
            <div className="flex items-center gap-2 px-2 py-1.5 bg-emerald-50 rounded-lg">
              <div className="w-5 h-5 bg-emerald-500 rounded flex items-center justify-center shrink-0">
                <span className="text-white text-xs font-bold">{org.name.charAt(0)}</span>
              </div>
              <span className="text-xs font-semibold text-emerald-800 truncate">{org.name}</span>
            </div>
          )}
        </div>

        {/* Nav */}
        <nav className="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
          <p className="px-3 pt-1 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
            Organisation
          </p>
          {nav.map((item) => (
            <NavLink
              key={item.href}
              item={item}
              exact={item.href === `/${orgSlug}/manage`}
            />
          ))}
        </nav>

        {/* User footer */}
        <div className="px-3 py-3 border-t border-gray-200">
          {user && (
            <p className="px-3 text-xs text-gray-400 truncate mb-1">{user.email}</p>
          )}
          <button
            onClick={handleLogout}
            className="flex items-center gap-2 px-3 py-2 w-full rounded-lg text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
            </svg>
            Sign out
          </button>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex-1 min-w-0 overflow-auto">
        <div className="max-w-6xl mx-auto px-8 py-8">{children}</div>
      </main>
    </div>
  );
}
