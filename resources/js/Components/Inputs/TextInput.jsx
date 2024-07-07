import { Form } from 'react-bootstrap';

export default function TextInput({ label, name, type = 'text', value, onChange, className, error, formControlProps }) {
  return (
    <Form.Group className={className} controlId={name}>
      <Form.Label>{label}</Form.Label>
      <Form.Control
        {...formControlProps}
        type={type}
        value={value || ''}
        onChange={e => onChange(e.target.value)}
        className={error ? 'is-invalid' : ''}
      />
      {error && <Form.Control.Feedback type="invalid">{error}</Form.Control.Feedback>}
    </Form.Group>
  );
}
