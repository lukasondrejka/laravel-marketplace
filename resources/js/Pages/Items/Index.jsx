import {Head, router} from '@inertiajs/react';
import {Alert, Button, Col, Container, Form, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import ItemCard from "@/Components/ItemCard.jsx";
import Sidebar from "@/Pages/Items/Partials/Sidebar.jsx";

export default function Index({ auth, items, categories }) {
    return (
        <Layout
            user={auth.user}
        >
            <Head title="Marketplace"/>

            <Container>
                <Row className="z-1">
                    <Col lg={3}>
                        <div className="my-4">
                            <Sidebar categories={categories} />
                        </div>
                    </Col>

                    <Col lg={9} className="mt-4">
                        {items.length === 0 && (
                            <Alert variant="danger" className="">
                                No items found
                            </Alert>
                        )}

                        {items.map(item => (
                            <ItemCard key={item.id} item={item} />
                        ))}
                    </Col>
                </Row>
            </Container>
        </Layout>
    );
}
