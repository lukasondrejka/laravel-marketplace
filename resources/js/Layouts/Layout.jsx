import Footer from '@/Layouts/Partials/Footer.jsx';
import Navbar from '@/Layouts/Partials/Navbar.jsx';
import { Container } from 'react-bootstrap';

export default function Layout({ user, children }) {
  return (
    <Container fluid className="px-0 min-vh-100 d-flex flex-column justify-content-between bd-highlight">
      <div className="d-flex flex-column bd-highlight flex-fill">
        <Navbar user={user} />
        <main className="d-flex flex-column bd-highlight flex-fill">{children}</main>
      </div>
      <div className="flex-shrink-1 bd-highlight">
        <Footer />
      </div>
    </Container>
  );
}
