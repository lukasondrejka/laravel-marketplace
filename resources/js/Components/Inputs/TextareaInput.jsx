import { Form } from 'react-bootstrap';

export default function TextareaInput({
  label,
  name,
  type = 'text',
  value,
  onChange,
  className,
  error,
  rows = 8,
  formControlProps,
}) {
  return (
    <Form.Group className={className} controlId={name}>
      <Form.Label>{label}</Form.Label>
      <Form.Control
        {...formControlProps}
        as="textarea"
        name={name}
        rows={rows}
        type={type}
        defaultValue={value}
        onChange={e => onChange(e.target.value)}
        className={error ? 'is-invalid' : ''}
      />
      {error && <Form.Control.Feedback type="invalid">{error}</Form.Control.Feedback>}
    </Form.Group>
  );
}
