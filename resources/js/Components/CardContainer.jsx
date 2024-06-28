import React from 'react';
import {Card, Container} from 'react-bootstrap';

export default function CardContainer({ children, header }) {
    return (
        <Container>
            <Card className="my-4 p-4">
                {header && <h1>{header}</h1>}
                {children}
            </Card>
        </Container>
    );
};
