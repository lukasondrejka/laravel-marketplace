import { useEffect } from 'react';
import Layout from "@/Layouts/Layout.jsx";
import { Form, Button, Container, Row, Col, Alert, Card } from 'react-bootstrap';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post(route('login'));
    };

    return (
        <Layout>
            <Head title="Log in" />

            {status && <Alert variant="success">{status}</Alert>}

            <Container className="my-5">
                <Row className="justify-content-md-center">
                    <Col lg="5" md="8">
                        <Card>
                            <Card.Body className="m-2">
                                <Card.Title><h2 className="text-center">Log in</h2></Card.Title>
                                <Form onSubmit={submit}>

                                    <Form.Group controlId="email" className="my-4">
                                        <Form.Label>Email</Form.Label>
                                        <Form.Control
                                            type="email"
                                            value={data.email}
                                            autoComplete="username"
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                        {errors.email && <Form.Text className="text-danger">{errors.email}</Form.Text>}
                                    </Form.Group>

                                    <Form.Group controlId="password" className="my-4">
                                        <Form.Label>Password</Form.Label>
                                        <Form.Control
                                            type="password"
                                            value={data.password}
                                            autoComplete="current-password"
                                            onChange={(e) => setData('password', e.target.value)}
                                        />
                                        {errors.password && <Form.Text className="text-danger">{errors.password}</Form.Text>}
                                    </Form.Group>

                                    <Form.Group controlId="remember" className="my-4">
                                        <Form.Check
                                            type="checkbox"
                                            label="Remember me"
                                            checked={data.remember}
                                            onChange={(e) => setData('remember', e.target.checked)}
                                        />
                                    </Form.Group>

                                    <div className="d-grid">
                                        <Button variant="primary" type="submit" disabled={processing}>
                                            {processing ? 'Logging in...' : 'Log in'}
                                        </Button>
                                    </div>

                                    {canResetPassword && (
                                        <div className="mt-3">
                                            <Link href={route('password.request')} className="text-decoration-none">
                                                Forgot your password?
                                            </Link>
                                        </div>
                                    )}
                                </Form>
                            </Card.Body>
                        </Card>
                    </Col>
                </Row>
            </Container>
        </Layout>
    );
}
