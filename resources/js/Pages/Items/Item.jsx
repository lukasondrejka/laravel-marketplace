import {Head, router} from '@inertiajs/react';
import {Alert, Button, Card, Col, Container, Form, Modal, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import {useState} from "react";
import ItemCard from "@/Components/ItemCard.jsx";

export default function Item({ auth, item }) {
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    return (
        <Layout
            user={auth.user}
        >
            <Head title={item.name}/>

            <Container>
                <Card className="item-card-block mb-3 mt-4">
                    <Row>
                        <Col md>
                            <Card.Body className="pl-0 p-4">
                                <h1 className="card-title">{item.title}</h1>
                                <p className="card-text"><small className="text-muted">{item.created_at} · {item.category.name}</small></p>
                                <p className="card-text">{item.description}</p>
                            </Card.Body>
                        </Col>
                    </Row>
                    <div className="clearfix px-4">
                        <div className="float-end">
                            <h3>{item.price} €</h3>
                        </div>
                    </div>

                    {(auth?.user?.id === item?.user_id) && (
                        <div className="p-4">
                            <Button href={route('items.edit', item.id)} variant="primary">
                                Edit
                            </Button>

                            <Button className="ms-3" variant="danger" onClick={() => setShowDeleteModal(true)}>
                                Delete
                            </Button>
                        </div>
                    )}

                    <Modal show={showDeleteModal} onHide={() => setShowDeleteModal(false)}>
                        <Modal.Header closeButton>
                            <Modal.Title>Delete Item</Modal.Title>
                        </Modal.Header>
                        <Modal.Body>Are you sure you want to delete this item?</Modal.Body>
                        <Modal.Footer>
                            <Button variant="secondary" onClick={() => setShowDeleteModal(false)}>
                                Close
                            </Button>
                            <Button variant="danger" >
                                Delete
                            </Button>
                        </Modal.Footer>
                    </Modal>
                </Card>
            </Container>
        </Layout>
    );
}
