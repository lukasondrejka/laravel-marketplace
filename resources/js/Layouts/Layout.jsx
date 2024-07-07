import Footer from '@/Layouts/Partials/Footer.jsx';
import Navbar from '@/Layouts/Partials/Navbar.jsx';
import {Container} from 'react-bootstrap';
import {ToastContainer as BootstrapToastContainer} from "react-bootstrap";
import Notification from "@/Layouts/Partials/Notification.jsx";
import {usePage} from "@inertiajs/react";


export default function Layout({ user, children }) {
  const {flash} = usePage().props;

  return (
    <Container fluid className="px-0 min-vh-100 d-flex flex-column justify-content-between bd-highlight">
      <div className="d-flex flex-column bd-highlight flex-fill">
        <Navbar user={user} />
        <main className="d-flex flex-column bd-highlight flex-fill">
            <BootstrapToastContainer className="d-inline-block" position="top-center" style={{ zIndex: 1, paddingTop: "100px" }} >
              {flash?.message && (<Notification message={flash.message} />)}
            </BootstrapToastContainer>
            {children}
        </main>
      </div>
      <div className="flex-shrink-1 bd-highlight">
        <Footer />
      </div>
    </Container>
  );
}
