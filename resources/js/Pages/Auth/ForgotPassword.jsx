import Layout from '@/Layouts/Layout';
import { Head, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Col, Container, Form, Row } from 'react-bootstrap';

export default function ForgotPassword({ status }) {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
  });

  const submit = e => {
    e.preventDefault();

    post(route('password.email'));
  };

  return (
    <Layout>
      <Head title="Forgot Password" />

      <Container className="my-5">
        <Row className="justify-content-md-center">
          <Col lg="5" md="8">
            <Card>
              <Card.Body className="m-2">
                <Card.Title>
                  <h2 className="text-center">Forgot Password</h2>
                </Card.Title>
                <div className="mb-4 text-sm text-gray-600">
                  Enter your email address and we will send you a password reset link.
                </div>

                {status && (
                  <Alert variant="success" className="mb-4">
                    {status}
                  </Alert>
                )}

                <Form onSubmit={submit}>
                  <Form.Group controlId="email" className="my-4">
                    <Form.Label>Email</Form.Label>
                    <Form.Control
                      type="email"
                      value={data.email}
                      autoComplete="username"
                      onChange={e => setData('email', e.target.value)}
                    />
                    {errors.email && <Form.Text className="text-danger">{errors.email}</Form.Text>}
                  </Form.Group>

                  <div className="d-grid">
                    <Button variant="primary" type="submit" disabled={processing}>
                      {processing ? 'Sending...' : 'Send Password Reset Link'}
                    </Button>
                  </div>
                </Form>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>
    </Layout>
  );
}
