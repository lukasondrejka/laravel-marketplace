import {Alert, Button, Card, Col, Container, Form, Modal, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import {useState} from "react";
import ItemCard from "@/Pages/Items/Partials/ItemCard.jsx";
import CardContainer from "@/Components/CardContainer.jsx";

export default function UserCard({ auth, user }) {
    return (
        <CardContainer header={user.name}>
            <p className="card-text">
                <small className="text-muted">
                    {user.items.length ? `${user.items.length} item${user.items.length > 1 ? 's' : ''}` : 'No items listed'}
                </small>
            </p>

            {(auth?.user?.id === user.id) && (
                <div className='mt-2'>
                    <Button href={route('profile.edit')} variant="primary">
                        Edit
                    </Button>
                </div>
            )}
        </CardContainer>
    );
}
