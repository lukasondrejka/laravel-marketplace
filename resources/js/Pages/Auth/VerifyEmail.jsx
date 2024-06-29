import { useState } from 'react';
import Layout from "@/Layouts/Layout.jsx";
import { Form, Button, Container, Row, Col, Alert, Card } from 'react-bootstrap';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <Layout>
            <Head title="Email Verification" />

            <Container className="my-5">
                <Row className="justify-content-md-center">
                    <Col lg="5" md="8">
                        <Card>
                            <Card.Body className="m-2">
                                <Card.Title><h2 className="text-center">Email Verification</h2></Card.Title>
                                <div className="mb-4 text-sm text-gray-600">
                                    Thanks for signing up! Before getting started, could you verify your email address by clicking on the
                                    link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                                </div>

                                {status === 'verification-link-sent' && (
                                    <Alert variant="success" className="mb-4">
                                        A new verification link has been sent to the email address you provided during registration.
                                    </Alert>
                                )}

                                <Form onSubmit={submit}>
                                    <div className="d-flex justify-content-between align-items-center mt-4">
                                        <Button variant="primary" type="submit" disabled={processing}>
                                            {processing ? 'Sending...' : 'Resend Verification Email'}
                                        </Button>

                                        <Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                            className="btn btn-link text-sm text-gray-600 hover:text-gray-900"
                                        >
                                            Log Out
                                        </Link>
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
