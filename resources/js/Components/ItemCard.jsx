import React from 'react';
import { Card, Row, Col } from 'react-bootstrap';

const ItemCard = ({ item }) => {
    return (
        <Card className="item-card-block mb-3">
            <Row>
                <Col md>
                    <Card.Body className="pl-0 p-4">
                        <a href={route('items.show', item.id)} className="item-title">
                            <Card.Title>{item.title}</Card.Title>
                        </a>
                        <Card.Text>
                            <small className="text-muted">
                                {item.created_at} · {item.category.name}
                            </small>
                        </Card.Text>
                        <Card.Text>{item.teaser}</Card.Text>
                    </Card.Body>
                    <div className="clearfix p-4">
                        <div className="float-end">
                            <h5>{item.price} €</h5>
                        </div>
                    </div>
                </Col>
            </Row>
        </Card>
    );
};

export default ItemCard;
