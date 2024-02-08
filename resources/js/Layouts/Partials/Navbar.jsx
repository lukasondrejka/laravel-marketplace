import {Container, Nav, NavDropdown, Navbar as BootstrapNavbar} from "react-bootstrap";
import {Link} from "@inertiajs/react";

export default function Navbar({ user=null }) {
    return (
        <BootstrapNavbar variant="light" sticky="top" expand="md" border="bottom">
            <Container>
                <BootstrapNavbar.Brand as={Link} href="/">
                    Marketplace
                </BootstrapNavbar.Brand>
                <BootstrapNavbar.Toggle aria-controls="basic-navbar-nav" />
                <BootstrapNavbar.Collapse id="basic-navbar-nav">
                    <Nav className="me-auto">
                        <Nav.Link as={Link} href={route('dashboard')} active={route().current('dashboard')}>
                            Dashboard
                        </Nav.Link>
                    </Nav>
                    <Nav>
                        {user ? (
                            <NavDropdown title={user.name} id="basic-nav-dropdown">
                                <NavDropdown.Item as={Link} href={route('profile.edit')}>
                                    Profile
                                </NavDropdown.Item>
                                <NavDropdown.Item as={Link} href={route('logout')} method="post">
                                    Log Out
                                </NavDropdown.Item>

                            </NavDropdown>
                        ) : (
                            <>
                                <Nav.Link as={Link} href={route('login')}>
                                    Log In
                                </Nav.Link>
                                <Nav.Link as={Link} href={route('register')}>
                                    Register
                                </Nav.Link>
                            </>
                        )}
                    </Nav>
                </BootstrapNavbar.Collapse>
            </Container>
        </BootstrapNavbar>
    );
}
