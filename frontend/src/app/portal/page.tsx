'use client';

import Link from 'next/link';
import { useAuthStore } from '@/store/authStore';

function QuickCard({ title, description, href, icon }: {
  title: string;
  description: string;
  href: string;
  icon: React.ReactNode;
}) {
  return (
    <Link
      href={href}
      className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-violet-200 transition-all group"
    >
      <div className="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center mb-3 group-hover:bg-violet-100 transition-colors">
        <svg className="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
          {icon}
        </svg>
      </div>
      <h3 className="text-sm font-semibold text-gray-900 mb-1">{title}</h3>
      <p className="text-xs text-gray-500">{description}</p>
    </Link>
  );
}

export default function PortalDashboardPage() {
  const { user } = useAuthStore();

  return (
    <>
      <div className="mb-8">
        <h1 className="text-2xl font-semibold text-gray-900">
          Welcome back{user?.email ? `, ${user.email.split('@')[0]}` : ''}
        </h1>
        <p className="mt-1 text-sm text-gray-500">Manage your memberships and account from here.</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <QuickCard
          title="My Subscriptions"
          description="View and manage your active memberships."
          href="/portal/subscriptions"
          icon={<path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />}
        />
        <QuickCard
          title="My Orders"
          description="View your payment history and receipts."
          href="/portal/orders"
          icon={<path strokeLinecap="round" strokeLinejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />}
        />
        <QuickCard
          title="My Profile"
          description="Update your personal details and preferences."
          href="/portal/profile"
          icon={<path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />}
        />
      </div>
    </>
  );
}
