import Layout from '@/Layouts/Layout.jsx';
import { Head } from '@inertiajs/react';
import { Container } from 'react-bootstrap';

export default function NotFound({ auth }) {
  return (
    <Layout user={auth.user}>
      <Head title="Not Found" />

      <Container className="py-5 my-auto justify-content-center">
        <h1 className="text-center">404 Not Found</h1>
        <p className="text-center">The page you are looking for does not exist</p>
      </Container>
    </Layout>
  );
}
