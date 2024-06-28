import {Head, Link, usePage} from '@inertiajs/react';
import {Alert, Col, Container, Row} from 'react-bootstrap';
import Layout from "@/Layouts/Layout.jsx";
import ItemCard from "@/Pages/Items/Partials/ItemCard.jsx";
import Sidebar from "@/Pages/Items/Partials/Sidebar.jsx";
import Pagination from 'react-bootstrap/Pagination';

export default function Index({ auth, items, categories }) {
    const {items: itemsPage} = usePage().props;

    console.log(itemsPage)
    return (
        <Layout
            user={auth.user}
        >
            <Head title="Items"/>

            <Container>
                <Row className="z-1">
                    <Col lg={3}>
                        <div className="my-4">
                            <Sidebar categories={categories} />
                        </div>
                    </Col>

                    <Col lg={9} className="mt-4">
                        {itemsPage.data.length === 0 ? (
                            <Alert variant="danger" className="">
                                No items found :(
                            </Alert>
                        ) : itemsPage.data.map(item => (
                            <ItemCard key={item.id} item={item} />
                        ))}

                        <Pagination className="justify-content-center">
                            {itemsPage.links.map((link, index) => (
                                <Pagination.Item key={index}  href={link.url} active={link.active} as={Link}>
                                    {link.label}
                                </Pagination.Item>
                            ))}
                        </Pagination>
                    </Col>
                </Row>
            </Container>
        </Layout>
    );
}
