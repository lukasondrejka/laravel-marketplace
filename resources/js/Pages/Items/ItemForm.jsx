import {Head, router, useForm} from '@inertiajs/react';
import {Alert, Button, Card, Col, Container, Form, Modal, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import {useState} from "react";

export default function Item({ auth, item = {}, categories }) {
    const { data, setData, errors, put, post, reset, processing, recentlySuccessful } = useForm({
        title: item.title || '',
        category_id: item.category_id || '',
        description: item.description || '',
        price: item.price || '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        if (item.id) {
            post(route('items.update', item.id));
        } else {
            post(route('items.create'));
        }
    }

    const title = item?.title ? `Edit ${item.title}` : 'New item';

    return (
        <Layout
            user={auth.user}
        >
            <Head title={title}/>

            <Container>
                <Card className="mb-3 mt-4">
                    <Form className="m-4" onSubmit={handleSubmit}>
                        <h2>{title}</h2>

                        <Form.Group className="mb-3 mt-3" controlId="titleInput">
                            <Form.Label>Title</Form.Label>
                            <Form.Control type="text" name="title" defaultValue={data.title} onChange={e => setData('title', e.target.value)} className={errors.title ? 'is-invalid' : ''} />
                            {errors.title && <Form.Control.Feedback type="invalid">{errors.title}</Form.Control.Feedback>}
                        </Form.Group>

                        <Form.Group className="mb-3" controlId="categorySelect">
                            <Form.Label>Category</Form.Label>
                            <Form.Select name="category" defaultValue={data.category_id} onChange={e => e.target.value === '-1' && setData('category_id', e.target.value)}>
                                <option value="-1" disabled selected={!item.category_id}></option>
                                {categories.map(category => (
                                    <option key={category.id} value={category.id}>{category.name}</option>
                                ))}
                            </Form.Select>
                        </Form.Group>

                        <Form.Group className="mb-3" controlId="descriptionInput">
                            <Form.Label>Description</Form.Label>
                            <Form.Control as="textarea" name="description" rows={8} defaultValue={data.description} onChange={e => setData('description', e.target.value)} className={errors.description ? 'is-invalid' : ''} />
                            {errors.description && <Form.Control.Feedback type="invalid">{errors.description}</Form.Control.Feedback>}
                        </Form.Group>

                        <Form.Group as={Col} md={3} className="mb-3" controlId="priceInput">
                            <Form.Label>Price</Form.Label>
                            <Form.Control type="number" min="0" name="price" defaultValue={data.price} onChange={e => setData('price', e.target.value)} className={errors.price ? 'is-invalid' : ''} />
                            {errors.price && <Form.Control.Feedback type="invalid">{errors.price}</Form.Control.Feedback>}
                        </Form.Group>

                        <Button variant="primary" type="submit">Submit</Button>
                    </Form>
                </Card>
            </Container>
        </Layout>
    );
}
