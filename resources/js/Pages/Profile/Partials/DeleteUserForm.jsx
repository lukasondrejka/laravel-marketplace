import { useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import { Button, Form, Modal } from 'react-bootstrap';

export default function DeleteUserForm({ className = '' }) {
  const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
  const passwordInput = useRef();

  const {
    data,
    setData,
    delete: destroy,
    processing,
    reset,
    errors,
  } = useForm({
    password: '',
  });

  const confirmUserDeletion = () => {
    setConfirmingUserDeletion(true);
  };

  const deleteUser = e => {
    e.preventDefault();

    destroy(route('profile.destroy'), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.current.focus(),
      onFinish: () => reset(),
    });
  };

  const closeModal = () => {
    setConfirmingUserDeletion(false);
    reset();
  };

  return (
    <section className={`space-y-6 ${className}`}>
      <header>
        <h2 className="text-lg font-medium text-gray-900">Delete Account</h2>
        <p className="mt-1 text-sm text-gray-600">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
      </header>

      <Button variant="danger" onClick={confirmUserDeletion}>
        Delete Account
      </Button>

      <Modal show={confirmingUserDeletion} onHide={closeModal}>
        <Modal.Header closeButton>
          <Modal.Title>Are you sure you want to delete your account?</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <p>
            Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to
            permanently delete your account.
          </p>
          <Form onSubmit={deleteUser} className="mt-4">
            <Form.Group className="mb-3">
              <Form.Label>Password</Form.Label>
              <Form.Control
                type="password"
                ref={passwordInput}
                value={data.password}
                onChange={e => setData('password', e.target.value)}
                placeholder="Password"
                required
              />
              {errors.password && <p className="text-danger mt-2">{errors.password}</p>}
            </Form.Group>
            <div className="d-flex justify-content-end">
              <Button variant="secondary" onClick={closeModal}>
                Cancel
              </Button>
              <Button variant="danger" className="ms-3" type="submit" disabled={processing}>
                Delete Account
              </Button>
            </div>
          </Form>
        </Modal.Body>
      </Modal>
    </section>
  );
}
