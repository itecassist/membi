import Link from 'next/link';
import { getOrgFrontendUrl } from '@/lib/subdomain';
import type { Organisation } from '@/types/api';

async function fetchOrganisations(): Promise<Organisation[]> {
  try {
    const res = await fetch(
      `${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api'}/organisations`,
      { cache: 'no-store' }
    );
    if (!res.ok) return [];
    const json = await res.json();
    return json.data ?? [];
  } catch {
    return [];
  }
}

export default async function HomePage() {
  const organisations = await fetchOrganisations();

  return (
    <>
      {/* Hero */}
      <section className="bg-gradient-to-b from-primary-50 to-white py-24 px-4">
        <div className="container mx-auto max-w-4xl text-center">
          <h1 className="text-5xl font-bold text-gray-900 leading-tight mb-6">
            Membership management{' '}
            <span className="text-primary-600">made simple</span>
          </h1>
          <p className="text-xl text-gray-500 mb-10 max-w-2xl mx-auto leading-relaxed">
            Membix helps clubs, societies, and associations manage members, subscriptions,
            events, and payments — all in one place.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              href="/register"
              className="px-8 py-3.5 bg-primary-600 text-white rounded-lg font-semibold text-lg hover:bg-primary-700 transition-colors shadow-md"
            >
              Start free trial
            </Link>
            <Link
              href="/pricing"
              className="px-8 py-3.5 border border-gray-300 text-gray-700 rounded-lg font-semibold text-lg hover:border-primary-400 hover:text-primary-600 transition-colors"
            >
              View pricing
            </Link>
          </div>
        </div>
      </section>

      {/* Organisation directory */}
      {organisations.length > 0 && (
        <section className="py-20 px-4 bg-gray-50">
          <div className="container mx-auto max-w-5xl">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-3">Find your organisation</h2>
              <p className="text-gray-500 text-lg">
                Select your club or society to sign in to your member portal.
              </p>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {organisations.map((org) => (
                <a
                  key={org.id}
                  href={`${getOrgFrontendUrl(org.seo_name)}/login`}
                  className="group bg-white rounded-xl border border-gray-200 p-6 hover:border-primary-400 hover:shadow-md transition-all flex flex-col"
                >
                  <div className="flex items-center gap-3 mb-3">
                    {org.logo ? (
                      // eslint-disable-next-line @next/next/no-img-element
                      <img src={org.logo} alt={org.name} className="w-10 h-10 rounded-lg object-cover" />
                    ) : (
                      <div className="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center shrink-0">
                        <span className="text-primary-700 font-bold text-sm uppercase">
                          {org.seo_name.slice(0, 2)}
                        </span>
                      </div>
                    )}
                    <div className="min-w-0">
                      <h3 className="font-semibold text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                        {org.name}
                      </h3>
                      <p className="text-xs text-gray-400">{org.seo_name}.membix</p>
                    </div>
                  </div>
                  {org.description && (
                    <p className="text-sm text-gray-500 line-clamp-2 mb-4">{org.description}</p>
                  )}
                  <div className="mt-auto flex items-center text-sm font-medium text-primary-600 group-hover:text-primary-700">
                    Go to portal
                    <svg className="ml-1 w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </a>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Feature highlights */}
      <section className="py-20 px-4">
        <div className="container mx-auto max-w-5xl">
          <h2 className="text-3xl font-bold text-gray-900 text-center mb-14">
            Everything your organisation needs
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {features.map((f) => (
              <div key={f.title} className="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div className="w-11 h-11 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                  <svg className="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                    {f.icon}
                  </svg>
                </div>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">{f.title}</h3>
                <p className="text-sm text-gray-500 leading-relaxed">{f.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA banner */}
      <section className="bg-primary-600 py-16 px-4">
        <div className="container mx-auto max-w-3xl text-center">
          <h2 className="text-3xl font-bold text-white mb-4">Ready to get started?</h2>
          <p className="text-primary-100 mb-8 text-lg">
            Join hundreds of organisations already using Membix.
          </p>
          <Link
            href="/register"
            className="inline-block px-8 py-3.5 bg-white text-primary-600 rounded-lg font-semibold text-lg hover:bg-primary-50 transition-colors shadow-md"
          >
            Create your free account
          </Link>
        </div>
      </section>
    </>
  );
}

      {/* Feature highlights */}
      <section className="py-20 px-4">
        <div className="container mx-auto max-w-5xl">
          <h2 className="text-3xl font-bold text-gray-900 text-center mb-14">
            Everything your organisation needs
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {features.map((f) => (
              <div key={f.title} className="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div className="w-11 h-11 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                  <svg className="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                    {f.icon}
                  </svg>
                </div>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">{f.title}</h3>
                <p className="text-sm text-gray-500 leading-relaxed">{f.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA banner */}
      <section className="bg-primary-600 py-16 px-4">
        <div className="container mx-auto max-w-3xl text-center">
          <h2 className="text-3xl font-bold text-white mb-4">Ready to get started?</h2>
          <p className="text-primary-100 mb-8 text-lg">
            Join hundreds of organisations already using Membix.
          </p>
          <Link
            href="/register"
            className="inline-block px-8 py-3.5 bg-white text-primary-600 rounded-lg font-semibold text-lg hover:bg-primary-50 transition-colors shadow-md"
          >
            Create your free account
          </Link>
        </div>
      </section>
    </>
  );
}

const features = [
  {
    title: 'Member management',
    description: 'Manage individual and group memberships with custom fields, classifications, and renewal workflows.',
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />,
  },
  {
    title: 'Subscriptions & billing',
    description: 'Flexible subscription types, tiered pricing, Direct Debit via GoCardless, and card payments via WorldPay.',
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />,
  },
  {
    title: 'Events & bookings',
    description: 'Organise events, manage sessions, track attendance, and handle bookings all within your member portal.',
    icon: <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />,
  },
];
