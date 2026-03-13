'use client';

import PageHeader from '@/components/ui/PageHeader';

export default function PortalSubscriptionsPage() {
  return (
    <>
      <PageHeader
        title="My Subscriptions"
        description="Your active and past memberships."
        backHref="/portal"
        backLabel="Dashboard"
      />

      <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div className="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
          <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
          </svg>
        </div>
        <h3 className="text-sm font-semibold text-gray-900 mb-1">No subscriptions yet</h3>
        <p className="text-sm text-gray-500">Your membership subscriptions will appear here once you have been enrolled.</p>
      </div>
    </>
  );
}
