import Layout from '@/Layouts/Layout.jsx';
import ItemCard from '@/Pages/Items/Partials/ItemCard.jsx';
import UserCard from '@/Pages/Profile/Partials/UserCard.jsx';
import { Head } from '@inertiajs/react';
import { Container } from 'react-bootstrap';

export default function Profile({ auth, user }) {
  return (
    <Layout user={auth.user}>
      <Head title={user.name} />

      <UserCard user={user} auth={auth} />

      {user.items.length && (
        <Container>
          <h2 className="text-center py-3">Items</h2>

          {user.items.map(item => (
            <ItemCard key={item.id} item={item} />
          ))}
        </Container>
      )}
    </Layout>
  );
}
