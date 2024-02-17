import React from 'react';
import {Card, Container} from 'react-bootstrap';

export default function CardContainer({ children }) {
    return (
        <Container>
            <Card className="mb-3 mt-4">
                {children}
            </Card>
        </Container>
    );
};
