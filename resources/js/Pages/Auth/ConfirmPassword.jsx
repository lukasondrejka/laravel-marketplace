import Layout from '@/Layouts/Layout';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { Button, Card, Col, Container, Form, Row } from 'react-bootstrap';

export default function ConfirmPassword() {
  const { data, setData, post, processing, errors, reset } = useForm({
    password: '',
  });

  useEffect(() => {
    return () => {
      reset('password');
    };
  }, []);

  const submit = e => {
    e.preventDefault();

    post(route('password.confirm'));
  };

  return (
    <Layout>
      <Head title="Confirm Password" />

      <Container className="my-5">
        <Row className="justify-content-md-center">
          <Col lg="5" md="8">
            <Card>
              <Card.Body className="m-2">
                <Card.Title>
                  <h2 className="text-center">Confirm Password</h2>
                </Card.Title>
                <div className="mb-4 text-sm text-gray-600">This is a secure area of the application. Please confirm your password before continuing.</div>

                <Form onSubmit={submit}>
                  <Form.Group controlId="password" className="my-4">
                    <Form.Label>Password</Form.Label>
                    <Form.Control type="password" value={data.password} autoComplete="current-password" onChange={e => setData('password', e.target.value)} />
                    {errors.password && <Form.Text className="text-danger">{errors.password}</Form.Text>}
                  </Form.Group>

                  <div className="d-grid">
                    <Button variant="primary" type="submit" disabled={processing}>
                      {processing ? 'Confirming...' : 'Confirm'}
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
