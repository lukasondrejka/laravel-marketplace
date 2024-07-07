import CardContainer from '@/Components/CardContainer.jsx';
import SelectInput from '@/Components/Inputs/SelectInput.jsx';
import TextareaInput from '@/Components/Inputs/TextareaInput.jsx';
import TextInput from '@/Components/Inputs/TextInput.jsx';
import Layout from '@/Layouts/Layout.jsx';
import { Head, useForm } from '@inertiajs/react';
import { Button, Col, Form, Row } from 'react-bootstrap';

export default function Item({ auth, item, categories }) {
  const { data, setData, errors, post } = useForm(item);

  function submit(e) {
    e.preventDefault();

    if (item?.id) {
      post(route('items.update', item.id));
    } else {
      post(route('items.create'));
    }
  }

  const title = item?.title ? `Edit ${item.title}` : 'New item';

  return (
    <Layout user={auth.user}>
      <Head title={title} />

      <CardContainer header={title}>
        <Form onSubmit={submit}>
          <TextInput
            className="mb-3"
            name="title"
            label="Title"
            value={data.title}
            onChange={v => setData('title', v)}
            error={errors.title}
          />

          <Row>
            <Col md="6">
              <SelectInput
                className="mb-3"
                name="category_id"
                label="Category"
                value={data.category_id}
                onChange={v => setData('category_id', v)}
                error={errors.category_id}
                options={[{ id: '', name: '', disabled: true }, ...categories]}
              />
            </Col>

            <Col md="6">
              <TextInput
                className="mb-3"
                name="price"
                label="Price"
                type="number"
                value={data.price}
                onChange={v => setData('price', v)}
                error={errors.price}
                formControlProps={{ min: 0 }}
              />
            </Col>
          </Row>

          <TextareaInput
            className="mb-3"
            name="description"
            label="Description"
            value={data.description}
            rows="8"
            onChange={v => setData('description', v)}
            error={errors.description}
          />

          <Button variant="primary" type="submit">
            Save
          </Button>
        </Form>
      </CardContainer>
    </Layout>
  );
}
