import {Head, router, useForm} from '@inertiajs/react';
import {Alert, Button, Card, Col, Container, Form, Modal, Nav, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import {useState} from "react";
import ItemCard from "@/Pages/Items/Partials/ItemCard.jsx";
import CardContainer from "@/Components/CardContainer.jsx";
import UserCard from "@/Pages/Profile/Partials/UserCard.jsx";

export default function Profile({ auth, user }) {

    console.log(user)

    return (
        <Layout
            user={auth.user}
        >
            <Head title={user.name}/>

            <UserCard user={user} auth={auth} />
        </Layout>
    );
}
