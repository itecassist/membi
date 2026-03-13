'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { useAuthStore } from '@/store/authStore';

export default function DashboardPage() {
  const { user, organisations, loadOrganisations } = useAuthStore();

  useEffect(() => {
    if (organisations.length === 0) {
      loadOrganisations().catch(() => {});
    }
  }, [organisations.length, loadOrganisations]);

  return (
    <>
      <div className="mb-8">
        <h1 className="text-2xl font-semibold text-gray-900">
          Welcome back{user?.email ? `, ${user.email.split('@')[0]}` : ''}
        </h1>
        <p className="mt-1 text-sm text-gray-500">Here&apos;s what&apos;s happening across the platform.</p>
      </div>

      {/* Quick links */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <QuickCard
          title="Organisations"
          description="View and manage all organisations on the platform."
          href="/admin/organisations"
          icon={<path strokeLinecap="round" strokeLinejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />}
        />
        <QuickCard
          title="New organisation"
          description="Add a new organisation to the Membix platform."
          href="/admin/organisations/new"
          icon={<path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />}
        />
      </div>
    </>
  );
}

function QuickCard({ title, description, href, icon }: {
  title: string;
  description: string;
  href: string;
  icon: React.ReactNode;
}) {
  return (
    <Link
      href={href}
      className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-primary-200 transition-all group"
    >
      <div className="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center mb-3 group-hover:bg-primary-100 transition-colors">
        <svg className="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
          {icon}
        </svg>
      </div>
      <h3 className="text-sm font-semibold text-gray-900 mb-1">{title}</h3>
      <p className="text-xs text-gray-500">{description}</p>
    </Link>
  );
}
