'use client';

import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuthStore } from '@/store/authStore';
import PageHeader from '@/components/ui/PageHeader';
import FormField from '@/components/ui/FormField';

const schema = z.object({
  email: z.string().email('Invalid email'),
});

type FormData = z.infer<typeof schema>;

export default function PortalProfilePage() {
  const { user } = useAuthStore();

  const {
    register,
    reset,
    formState: { errors },
  } = useForm<FormData>({ resolver: zodResolver(schema) });

  useEffect(() => {
    if (user) {
      reset({ email: user.email });
    }
  }, [user, reset]);

  return (
    <>
      <PageHeader
        title="My Profile"
        description="Your account details."
        backHref="/portal"
        backLabel="Dashboard"
      />

      <div className="max-w-2xl">
        <div className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
          <div className="flex items-center gap-4 pb-4 border-b border-gray-100">
            <div className="w-14 h-14 bg-violet-100 rounded-full flex items-center justify-center">
              <span className="text-violet-700 text-lg font-bold">
                {user?.email?.charAt(0).toUpperCase() ?? '?'}
              </span>
            </div>
            <div>
              <p className="text-sm font-semibold text-gray-900">{user?.email}</p>
              <p className="text-xs text-gray-400 mt-0.5">Member account</p>
            </div>
          </div>

          <FormField
            as="input"
            type="email"
            label="Email address"
            id="email"
            error={errors.email?.message}
            {...register('email')}
            disabled
          />

          <div className="pt-2">
            <p className="text-xs text-gray-400">
              To update your profile details, please contact your organisation administrator.
            </p>
          </div>
        </div>
      </div>
    </>
  );
}
