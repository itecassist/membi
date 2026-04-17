'use client';

import { Suspense } from 'react';
import { useRouter, useSearchParams, useParams } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import Link from 'next/link';
import { useAuthStore } from '@/store/authStore';

const schema = z.object({
  email: z.string().email('Invalid email address'),
  password: z.string().min(1, 'Password is required'),
});

type FormData = z.infer<typeof schema>;

function OrgLoginPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { orgSlug } = useParams<{ orgSlug: string }>();
  const login = useAuthStore((s) => s.login);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
    setError,
  } = useForm<FormData>({ resolver: zodResolver(schema) });

  const onSubmit = async (data: FormData) => {
    try {
      await login(data);
      const redirect = searchParams.get('redirect');
      if (redirect) {
        router.push(redirect);
        return;
      }
      const { user } = useAuthStore.getState();
      if (user?.is_admin) {
        router.push('/admin/dashboard');
      } else {
        router.push(`/${orgSlug}/portal`);
      }
    } catch (err: unknown) {
      const message =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Invalid credentials';
      setError('root', { message });
    }
  };

  return (
    <div className="bg-white shadow-sm rounded-xl px-8 py-10">
      {/* Org context banner */}
      <div className="mb-6 flex items-center gap-2.5 rounded-lg bg-primary-50 border border-primary-200 px-4 py-3">
        <div className="w-8 h-8 rounded-md bg-primary-100 flex items-center justify-center shrink-0">
          <span className="text-primary-700 font-bold text-xs uppercase">{orgSlug.slice(0, 2)}</span>
        </div>
        <div>
          <p className="text-xs text-primary-500 leading-none mb-0.5">Signing in to</p>
          <p className="text-sm font-semibold text-primary-800 capitalize">{orgSlug}</p>
        </div>
      </div>

      <h1 className="text-2xl font-semibold text-gray-900 mb-1">Sign in</h1>
      <p className="text-sm text-gray-500 mb-8">
        Access your {orgSlug} member portal
      </p>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-5" noValidate>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="email">
            Email
          </label>
          <input
            id="email"
            type="email"
            autoComplete="email"
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            {...register('email')}
          />
          {errors.email && (
            <p className="mt-1 text-xs text-red-600">{errors.email.message}</p>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1" htmlFor="password">
            Password
          </label>
          <input
            id="password"
            type="password"
            autoComplete="current-password"
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            {...register('password')}
          />
          {errors.password && (
            <p className="mt-1 text-xs text-red-600">{errors.password.message}</p>
          )}
        </div>

        {errors.root && (
          <p className="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
            {errors.root.message}
          </p>
        )}

        <button
          type="submit"
          disabled={isSubmitting}
          className="w-full bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {isSubmitting ? 'Signing in…' : 'Sign in'}
        </button>
      </form>

      <p className="mt-6 text-center text-xs text-gray-400">
        Not your organisation?{' '}
        <Link href="/login" className="text-gray-500 hover:underline">
          Find yours
        </Link>
      </p>
    </div>
  );
}

export default function OrgLoginPage() {
  return (
    <Suspense>
      <OrgLoginPageContent />
    </Suspense>
  );
}
