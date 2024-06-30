import { Link, useForm } from '@inertiajs/react';
import { Navbar as BootstrapNavbar, Container, Nav } from 'react-bootstrap';

export default function Navbar({ user }) {
  const { post } = useForm({});

  function logout() {
    post(route('logout'));
  }

  return (
    <BootstrapNavbar variant="light" sticky="top" expand="md" border="bottom">
      <Container>
        <BootstrapNavbar.Brand as={Link} href="/">
          Marketplace
        </BootstrapNavbar.Brand>
        <BootstrapNavbar.Toggle aria-controls="basic-navbar-nav" />
        <BootstrapNavbar.Collapse id="basic-navbar-nav">
          <Nav className="me-auto">
            {/*<Nav.Link as={Link} href={route('dashboard')} active={route().current('dashboard')}>*/}
            {/*    Dashboard*/}
            {/*</Nav.Link>*/}
          </Nav>
          <Nav>
            {user ? (
              <>
                <Nav.Link as={Link} href={route('profile.show', user.id)}>
                  Profile
                </Nav.Link>
                <Nav.Link onClick={logout}>Log Out</Nav.Link>
              </>
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
