import {Head, useForm} from '@inertiajs/react';
import {Button, Card, Col, Container, Form } from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import CardContainer from "@/Components/CardContainer.jsx";

export default function Item({ auth, item = {}, categories }) {
    const { data, setData, errors, put, post, reset, processing, recentlySuccessful } = useForm({
        title: item.title || '',
        category_id: item.category_id || '',
        description: item.description || '',
        price: item.price || '',
    });

    function submit(e) {
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

            <CardContainer header={title}>
                <Form onSubmit={submit}>
                    <Form.Group className="mb-3 mt-3" controlId="titleInput">
                        <Form.Label>Title</Form.Label>
                        <Form.Control type="text" name="title" defaultValue={data.title} onChange={e => setData('title', e.target.value)} className={errors.title ? 'is-invalid' : ''} />
                        {errors.title && <Form.Control.Feedback type="invalid">{errors.title}</Form.Control.Feedback>}
                    </Form.Group>

                    <Form.Group className="mb-3" controlId="categorySelect">
                        <Form.Label>Category</Form.Label>
                        <Form.Select name="category" defaultValue={data.category_id} className={errors.category_id ? 'is-invalid' : ''} onChange={e => e.target.value !== '' && setData('category_id', e.target.value)}>
                            <option value="" disabled></option>
                            {categories.map(category => (
                                <option key={category.id} value={category.id}>{category.name}</option>
                            ))}
                        </Form.Select>
                        {errors.category_id && <Form.Control.Feedback type="invalid" className="d-block">{errors.category_id}</Form.Control.Feedback>}
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

                    <Button variant="primary" type="submit">Save</Button>
                </Form>
            </CardContainer>
        </Layout>
    );
}
