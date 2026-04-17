'use client';

import { Suspense } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { useState } from 'react';

function OrgFinder() {
  const [value, setValue] = useState('');
  const router = useRouter();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const slug = value.trim().toLowerCase().replace(/\s+/g, '-');
    if (slug) {
      router.push(`/${slug}/login`);
    }
  };

  return (
    <div className="bg-white shadow-sm rounded-xl px-8 py-10">
      <h1 className="text-2xl font-semibold text-gray-900 mb-1">Sign in</h1>
      <p className="text-sm text-gray-500 mb-8">
        Enter your organisation&apos;s short name to continue.
      </p>
      <form onSubmit={handleSubmit} className="space-y-5">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Organisation address
          </label>
          <div className="flex items-center rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
            <span className="px-3 py-2 bg-gray-50 border-r border-gray-300 text-sm text-gray-400 select-none whitespace-nowrap">
              {process.env.NEXT_PUBLIC_APP_DOMAIN ? `${process.env.NEXT_PUBLIC_APP_DOMAIN}/` : 'localhost/'}
            </span>
            <input
              type="text"
              value={value}
              onChange={(e) => setValue(e.target.value)}
              placeholder="river"
              autoFocus
              className="flex-1 px-3 py-2 text-sm outline-none"
            />
          </div>
        </div>
        <button
          type="submit"
          disabled={!value.trim()}
          className="w-full bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          Continue
        </button>
      </form>
      <p className="mt-6 text-center text-sm text-gray-500">
        New to Membix?{' '}
        <Link href="/register" className="text-indigo-600 hover:underline font-medium">
          Create an organisation
        </Link>
      </p>
    </div>
  );
}

function LoginPageContent() {
  return <OrgFinder />;
}

export default function LoginPage() {
  return (
    <Suspense>
      <LoginPageContent />
    </Suspense>
  );
}

