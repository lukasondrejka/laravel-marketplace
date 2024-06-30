import CardContainer from '@/Components/CardContainer.jsx';
import Layout from '@/Layouts/Layout.jsx';
import UserCard from '@/Pages/Profile/Partials/UserCard.jsx';
import { formatDate } from '@/utils.js';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Button, Modal } from 'react-bootstrap';

export default function Item({ auth, item }) {
  const { post } = useForm();
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  function remove() {
    post(route('items.destroy', item.id));
  }

  return (
    <Layout user={auth.user}>
      <Head title={item.name} />

      <CardContainer header={item.title}>
        <p className="card-text">
          <small className="text-muted">
            Created at {formatDate(item.created_at)}
            {item.created_at !== item.updated_at && (
              <>
                <span> · </span> Updated at {formatDate(item.updated_at)}
              </>
            )}
            <span> · </span> {item.category.name}
          </small>
        </p>

        <div dangerouslySetInnerHTML={{ __html: item.rich_text_description }}></div>

        <div className="clearfix mt-3">
          <div className="float-end">
            <h3>{item.price} €</h3>
          </div>
        </div>

        {auth?.user?.id === item?.user_id && (
          <div className="mt-2">
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

      <UserCard user={item.user} auth={auth} />
    </Layout>
  );
}
