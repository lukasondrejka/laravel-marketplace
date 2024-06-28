import {Head, router, useForm} from '@inertiajs/react';
import {Alert, Button, Card, Col, Container, Form, Modal, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import {useState} from "react";
import ItemCard from "@/Pages/Items/Partials/ItemCard.jsx";
import CardContainer from "@/Components/CardContainer.jsx";
import {formatDate} from "@/utils.js";

export default function Item({ auth, item }) {
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const { post } = useForm({});

    function remove() {
        post(route('items.destroy', item.id));
    }

    console.log(item);

    return (
        <Layout
            user={auth.user}
        >
            <Head title={item.name}/>

            <CardContainer header={item.title}>
                <p className="card-text">
                    <small className="text-muted">{formatDate(item.created_at)} · {item.category.name}</small>
                </p>

                <div dangerouslySetInnerHTML={{__html: item.rich_text_description}}></div>

                <div className="clearfix px-4">
                    <div className="float-end">
                        <h3>{item.price} €</h3>
                    </div>
                </div>

                {(auth?.user?.id === item?.user_id) && (
                    <div className='mt-2'>
                        <Button href={route('items.edit', item.id)} variant="primary">
                            Edit
                        </Button>

                        <Button className="ms-2" variant="danger" onClick={() => setShowDeleteModal(true)}>
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
                        <Button variant="danger" onClick={remove}>
                            Delete
                        </Button>
                    </Modal.Footer>
                </Modal>
            </CardContainer>
        </Layout>
    );
}
