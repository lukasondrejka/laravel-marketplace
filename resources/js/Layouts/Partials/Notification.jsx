import { useState } from 'react';
import { Toast } from 'react-bootstrap';

export default function Notification({ message, fadeTime = 3000 }) {
  const [show, setShow] = useState(true);

  return (
    <Toast onClose={() => setShow(false)} show={show} delay={fadeTime} autohide className="mb-3">
      <Toast.Header closeButton={true}>
        <span className="me-auto">{message}</span>
      </Toast.Header>
    </Toast>
  );
}
