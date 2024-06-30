import Layout from '@/Layouts/Layout.jsx';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { Button, Card, Col, Container, Form, Row } from 'react-bootstrap';

export default function ResetPassword({ token, email }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    token: token,
    email: email,
    password: '',
    password_confirmation: '',
  });

  useEffect(() => {
    return () => {
      reset('password', 'password_confirmation');
    };
  }, []);

  const submit = e => {
    e.preventDefault();

    post(route('password.store'));
  };

  return (
    <Layout>
      <Head title="Reset Password" />

      <Container className="my-5">
        <Row className="justify-content-md-center">
          <Col lg="5" md="8">
            <Card>
              <Card.Body className="m-2">
                <Card.Title>
                  <h2 className="text-center">Reset Password</h2>
                </Card.Title>
                <Form onSubmit={submit}>
                  <Form.Group controlId="email" className="my-4">
                    <Form.Label>Email</Form.Label>
                    <Form.Control type="email" value={data.email} autoComplete="username" onChange={e => setData('email', e.target.value)} />
                    {errors.email && <Form.Text className="text-danger">{errors.email}</Form.Text>}
                  </Form.Group>

                  <Form.Group controlId="password" className="my-4">
                    <Form.Label>Password</Form.Label>
                    <Form.Control type="password" value={data.password} autoComplete="new-password" onChange={e => setData('password', e.target.value)} />
                    {errors.password && <Form.Text className="text-danger">{errors.password}</Form.Text>}
                  </Form.Group>

                  <Form.Group controlId="password_confirmation" className="my-4">
                    <Form.Label>Confirm Password</Form.Label>
                    <Form.Control
                      type="password"
                      value={data.password_confirmation}
                      autoComplete="new-password"
                      onChange={e => setData('password_confirmation', e.target.value)}
                    />
                    {errors.password_confirmation && <Form.Text className="text-danger">{errors.password_confirmation}</Form.Text>}
                  </Form.Group>

                  <div className="d-grid">
                    <Button variant="primary" type="submit" disabled={processing}>
                      {processing ? 'Resetting...' : 'Reset Password'}
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
