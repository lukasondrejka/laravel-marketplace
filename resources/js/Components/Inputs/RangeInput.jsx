import { Col, Form, Row } from 'react-bootstrap';

export default function RangeInput({
  label,
  nameMin,
  nameMax,
  type = 'number',
  valueMin,
  valueMax,
  onChangeMin,
  onChangeMax,
  className,
  error,
  formControlPropsMin,
  formControlPropsMax,
}) {
  return (
    <Form.Group className={className}>
      <Form.Label>{label}</Form.Label>
      <Row>
        <Col>
          <Form.Control
            {...formControlPropsMin}
            type={type}
            value={valueMin}
            name={nameMin}
            onChange={e => onChangeMin(e.target.value)}
            className={error ? 'is-invalid' : ''}
          />
        </Col>
        <Col>
          <Form.Control
            {...formControlPropsMax}
            type={type}
            value={valueMax}
            name={nameMax}
            onChange={e => onChangeMax(e.target.value)}
            className={error ? 'is-invalid' : ''}
          />
        </Col>
      </Row>
      {error && (
        <Form.Control.Feedback type="invalid" className="d-block">
          {error}
        </Form.Control.Feedback>
      )}
    </Form.Group>
  );
}
