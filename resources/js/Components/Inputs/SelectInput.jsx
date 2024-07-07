import { Form } from 'react-bootstrap';

export default function SelectInput({ label, name, options, value, onChange, className, error }) {
  return (
    <Form.Group className={className} controlId={name}>
      <Form.Label>{label}</Form.Label>
      <Form.Select
        name={name}
        value={value}
        className={error ? 'is-invalid' : ''}
        onChange={e => onChange(e.target.value)}
      >
        {options.map(option => (
          <option key={option.id} value={option.id} disabled={option.disabled} selected={option.selected}>
            {option.name}
          </option>
        ))}
      </Form.Select>
      {error && (
        <Form.Control.Feedback type="invalid" className="d-block">
          {error}
        </Form.Control.Feedback>
      )}
    </Form.Group>
  );
}
