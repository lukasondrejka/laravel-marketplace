import { Head } from '@inertiajs/react';
import { Button, Col, Container, Form, Row } from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";

export default function Home({ auth }) {


    return (
        <Layout
            user={auth.user}
        >
            <Head title="Home"/>

            <Container>
                <Row className="justify-content-center">
                    <Col lg={6} md={9} className="mt-4">
                        <Form.Control type="text" placeholder="" size="lg" />
                    </Col>
                    <Col lg={2} md={3} className="mt-4">
                        <Button variant="primary" size="lg" block>Search</Button>
                    </Col>
                </Row>
            </Container>
        </Layout>
    );
}
