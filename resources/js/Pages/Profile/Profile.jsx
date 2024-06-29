import {Head, router, useForm} from '@inertiajs/react';
import {Alert, Button, Card, Col, Container, Form, Modal, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import {useState} from "react";
import ItemCard from "@/Pages/Items/Partials/ItemCard.jsx";
import CardContainer from "@/Components/CardContainer.jsx";
import UserCard from "@/Pages/Profile/Partials/UserCard.jsx";

export default function Profile({ auth, user }) {
    console.log(user.items);
    return (
        <Layout
            user={auth.user}
        >
            <Head title={user.name}/>

            <UserCard user={user} auth={auth} />

            {user.items.length && (
                <Container>
                    <h2 className="text-center py-3">Items</h2>

                    {user.items.map(item => (
                        <ItemCard key={item.id} item={item}/>
                    ))}
                </Container>
            )}


        </Layout>
    );
}
