import Pagination from '@/Components/Pagination.jsx';
import Layout from '@/Layouts/Layout.jsx';
import ItemCard from '@/Pages/Items/Partials/ItemCard.jsx';
import Sidebar from '@/Pages/Items/Partials/Sidebar.jsx';
import { Head, usePage } from '@inertiajs/react';
import { Alert, Col, Container, Row } from 'react-bootstrap';

export default function Items({ auth, categories }) {
  const { items: itemsPage } = usePage().props;

  return (
    <Layout user={auth.user}>
      <Head title="Items" />

      <Container>
        <Row className="z-1">
          <Col lg={3}>
            <div className="mt-4 mb-4 sidebar">
              <Sidebar categories={categories} />
            </div>
          </Col>

          <Col lg={9} className="mt-2">
            {itemsPage.data.length === 0 ? (
              <Alert variant="danger" className="">
                No items found :(
              </Alert>
            ) : (
              itemsPage.data.map(item => <ItemCard key={item.id} item={item} />)
            )}

            {itemsPage.last_page > 1 && <Pagination links={itemsPage.links} />}
          </Col>
        </Row>
      </Container>
    </Layout>
  );
}
