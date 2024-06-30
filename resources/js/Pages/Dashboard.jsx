import Layout from '@/Layouts/Layout.jsx';
import { Head } from '@inertiajs/react';
import { Container } from 'react-bootstrap';

export default function Dashboard({ auth }) {
  return (
    <Layout user={auth.user}>
      <Head title="Dashboard" />

      <Container className="my-5">
        <p>Dashboard</p>
      </Container>
    </Layout>
  );
}
