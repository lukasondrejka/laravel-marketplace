import { Container } from 'react-bootstrap';
import Navbar from "@/Layouts/Partials/Navbar.jsx";
import Footer from "@/Layouts/Partials/Footer.jsx";

export default function Layout({ user, children }) {
    return (
        <Container fluid className="px-0 min-vh-100 d-flex flex-column justify-content-between">
            <div>
                <Navbar user={user} />
                <main>
                    {children}
                </main>
            </div>
            <Footer />
        </Container>
    );
}
