import CardContainer from '@/Components/CardContainer.jsx';
import TextareaInput from '@/Components/Inputs/TextareaInput.jsx';
import TextInput from '@/Components/Inputs/TextInput.jsx';
import { Transition } from '@headlessui/react';
import { Link, useForm } from '@inertiajs/react';
import { Button, Col, Form, Row } from 'react-bootstrap';

export default function EditUserCard({ user, mustVerifyEmail, status }) {
  // const user = usePage().props.auth.user;

  const { data, setData, patch, errors, processing, get} = useForm(user);

  const submit = e => {
    e.preventDefault();
    patch(route('profile.update'));
  };

  return (
    <CardContainer>
      <header>
        <h2 className="text-center">Profile</h2>
      </header>
      <Form onSubmit={submit}>
        <Row>
          <Col md="6">
            <TextInput
              className="mb-3"
              name="name"
              label="Name"
              value={data.name}
              onChange={v => setData('name', v)}
              error={errors.name}
              formControlProps={{ autoComplete: 'name', required: true }}
            />
          </Col>
          <Col md="6">
            <TextInput
              className="mb-3"
              name="email"
              label="Email"
              type="email"
              value={data.email}
              onChange={v => setData('email', v)}
              error={errors.email}
              formControlProps={{ autoComplete: 'email', required: true }}
            />
          </Col>
          <Col md="6">
            <TextInput
              className="mb-3"
              name="location"
              label="Location"
              value={data.location}
              onChange={v => setData('location', v)}
              error={errors.location}
              formControlProps={{ autoComplete: 'address-level1' }}
            />
          </Col>
          <Col md="6">
            <TextInput
              className="mb-3"
              name="phone_number"
              label="Phone"
              type="tel"
              value={data.phone_number}
              onChange={v => setData('phone_number', v)}
              error={errors.phone_number}
              formControlProps={{ autoComplete: 'tel' }}
            />
          </Col>
        </Row>

        <TextareaInput
          className="mb-3"
          name="bio"
          label="Bio"
          value={data.bio}
          onChange={v => setData('bio', v)}
          error={errors.bio}
          rows={4}
        />

        <div className="d-flex align-items-center gap-4">
          <Button variant="primary" type="submit" disabled={processing}>
            Save
          </Button>
        </div>
      </Form>
    </CardContainer>
  );
}
