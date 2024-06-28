import React from 'react';
import { Card, Row, Col } from 'react-bootstrap';
import CardContainer from "@/Components/CardContainer.jsx";
import {formatDate} from "@/utils.js";

const ItemCard = ({ item }) => {
    return (
        <CardContainer>
            <Card.Body className="pl-0 p-4">
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
            <div className="clearfix pt-2">
                <div className="float-end">
                    <h5>{item.price} €</h5>
                </div>
            </div>
        </CardContainer>
    );
};

export default ItemCard;
