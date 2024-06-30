import { formatDate } from '@/utils.js';
import { Card } from 'react-bootstrap';

const ItemCard = ({ item }) => {
  return (
    <Card className="my-4 p-4">
      <Card.Body>
        <a href={route('items.show', item.id)} className="item-title">
          <Card.Title>{item.title}</Card.Title>
        </a>
        <Card.Text>
          <small className="text-muted">
            {formatDate(item.created_at)} · {item.category.name}
          </small>
        </Card.Text>
        <Card.Text>{item.teaser}</Card.Text>
      </Card.Body>
      <div className="clearfix pt-2 pr-4">
        <div className="float-end">
          <h5>{item.price} €</h5>
        </div>
      </div>
    </Card>
  );
};

export default ItemCard;
