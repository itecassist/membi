'use client';

import { useEffect } from 'react';
import { useParams } from 'next/navigation';
import { useAuthStore } from '@/store/authStore';
import { organisationsApi } from '@/lib/api/organisations';

export default function OrgLayout({ children }: { children: React.ReactNode }) {
  const { id } = useParams<{ id: string }>();
  const { currentOrganisation, setCurrentOrganisation } = useAuthStore();

  useEffect(() => {
    // Fetch and set current org context whenever the id changes
    if (!id) return;
    if (currentOrganisation?.id === id) return; // already loaded

    organisationsApi.get(id).then(({ data }) => {
      setCurrentOrganisation(data.data);
    }).catch(() => {});
  }, [id, currentOrganisation?.id, setCurrentOrganisation]);

  return <>{children}</>;
}
