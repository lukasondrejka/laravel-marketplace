import { Head, useForm } from '@inertiajs/react';
import { Button, Col, Container, Form, Row } from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";

export default function Home({ auth }) {
    const { setData, get } = useForm({ search: '' });

    const submit = (e) => {
        e.preventDefault();

        get(route('items.items'));
    };
    return (
        <Layout
            user={auth.user}
        >
            <Head title="Home"/>

            <form className="my-auto" onSubmit={submit}>
                <Container>
                    <Row className="justify-content-center my-4">
                        <Col lg={6} md={9}>
                            <Form.Control type="text" size="lg" onChange={e => setData('search', e.target.value)} />
                        </Col>
                        <Col lg={2} md={3} className="mt-4 mt-md-0">
                            <Button variant="primary" size="lg" block type="submit">Search</Button>
                        </Col>
                    </Row>
                </Container>
            </form>
        </Layout>
    );
}
