import Layout from '@/Layouts/Layout.jsx';
import { Head } from '@inertiajs/react';
import { Card, Col, Container, Row } from 'react-bootstrap';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({ auth, mustVerifyEmail, status }) {
  return (
    <Layout user={auth.user}>
      <Head title="Profile" />
      <Container className="py-12">
        <Row className="justify-content-center">
          <Col lg={8}>
            <Card className="p-4 my-4">
              <Card.Body>
                <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
              </Card.Body>
            </Card>
            <Card className="p-4 mb-4">
              <Card.Body>
                <UpdatePasswordForm />
              </Card.Body>
            </Card>
            <Card className="p-4 mb-4">
              <Card.Body>
                <DeleteUserForm />
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>
    </Layout>
  );
}
