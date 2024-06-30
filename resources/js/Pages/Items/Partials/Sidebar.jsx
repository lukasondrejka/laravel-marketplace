import { Link, useForm } from '@inertiajs/react';
import { Button, Col, Form, Row } from 'react-bootstrap';

export default function Sidebar({ categories }) {
  const params = new URLSearchParams(window.location.search);

  const { data, setData, get, errors, setError } = useForm({
    search: params.get('search') || '',
    category_id: params.get('category_id') || '',
    price_min: params.get('price_min') || '',
    price_max: params.get('price_max') || '',
  });

  function submit(e) {
    e.preventDefault();

    if (data.price_min && data.price_max && parseInt(data.price_min) > parseInt(data.price_max)) {
      setError('price_min', 'Min price must be less than max price');
      return;
    } else {
      setError('price_min', '');
    }

    get(route('items.items'));
  }

  return (
    <div className="z-0">
      <form onSubmit={submit}>
        <Form.Group className="mb-3">
          <Form.Label>{'Search'}</Form.Label>
          <Form.Control type="text" placeholder="" value={data.search} name="search" onChange={e => setData('search', e.target.value)} />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>{'Price'}</Form.Label>
          <Row>
            <Col>
              <Form.Control
                type="number"
                min="0"
                placeholder="Min"
                value={data.price_min}
                name="price_min"
                onChange={e => setData('price_min', e.target.value)}
                className={errors.price_min ? 'is-invalid' : ''}
              />
            </Col>
            <Col>
              <Form.Control
                type="number"
                min="0"
                placeholder="Max"
                value={data.price_max}
                name="price_max"
                onChange={e => setData('price_max', e.target.value)}
                className={errors.price_min ? 'is-invalid' : ''}
              />
            </Col>
          </Row>
          {errors.price_min && (
            <Form.Control.Feedback type="invalid" className="d-block">
              Min price must be less than max price
            </Form.Control.Feedback>
          )}
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Category</Form.Label>
          <Form.Select value={data.category_id} name="category_id" onChange={e => setData('category_id', e.target.value)}>
            <option value="">All</option>
            {categories.map(category => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </Form.Select>
        </Form.Group>

        <div className="my-4 d-grid">
          <Button type="submit" variant="primary" size="lg" block>
            Search
          </Button>
        </div>

        <div className="my-4 d-grid">
          <Link href={route('items.create')} className="btn btn-success btn-block">
            List new item
          </Link>
        </div>
      </form>
    </div>
  );
}
