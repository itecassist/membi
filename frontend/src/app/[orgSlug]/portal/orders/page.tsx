'use client';

import { useParams } from 'next/navigation';
import PageHeader from '@/components/ui/PageHeader';

export default function PortalOrdersPage() {
  const { orgSlug } = useParams<{ orgSlug: string }>();

  return (
    <>
      <PageHeader
        title="My Orders"
        description="Your payment history and receipts."
        backHref={`/${orgSlug}/portal`}
        backLabel="Dashboard"
      />

      <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div className="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
          <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
        </div>
        <h3 className="text-sm font-semibold text-gray-900 mb-1">No orders yet</h3>
        <p className="text-sm text-gray-500">Your payment history will appear here once you make a payment.</p>
      </div>
    </>
  );
}
