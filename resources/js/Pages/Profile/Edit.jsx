import Layout from '@/Layouts/Layout.jsx';
import { Head } from '@inertiajs/react';
import EditUserCard from './Partials/EditUserCard.jsx';

export default function Edit({ auth, user, mustVerifyEmail, status }) {
  return (
    <Layout user={auth.user}>
      <Head title="Profile" />
      <EditUserCard user={user} mustVerifyEmail={mustVerifyEmail} status={status} />
    </Layout>
  );
}
