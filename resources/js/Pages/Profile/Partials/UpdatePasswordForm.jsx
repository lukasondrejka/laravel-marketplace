import { useRef } from 'react';
import { Button, Form } from 'react-bootstrap';
import { useForm } from '@inertiajs/react';

export default function UpdatePasswordForm({ className = '' }) {
    const passwordInput = useRef();
    const currentPasswordInput = useRef();

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current.focus();
                }
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Password</h2>
            </header>
            <Form onSubmit={updatePassword} className="mt-6">
                <Form.Group className="mb-3">
                    <Form.Label htmlFor="current_password">Current Password</Form.Label>
                    <Form.Control
                        className={errors.current_password ? 'is-invalid' : ''}
                        id="current_password"
                        ref={currentPasswordInput}
                        value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)}
                        type="password"
                        autoComplete="current-password"
                    />
                    {errors.current_password && <p className="text-danger">{errors.current_password}</p>}
                </Form.Group>
                <Form.Group className="mb-3">
                    <Form.Label htmlFor="password">New Password</Form.Label>
                    <Form.Control
                        className={errors.password ? 'is-invalid' : ''}
                        id="password"
                        ref={passwordInput}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        type="password"
                        autoComplete="new-password"
                    />
                    {errors.password && <p className="text-danger">{errors.password}</p>}
                </Form.Group>
                <Form.Group className="mb-3">
                    <Form.Label htmlFor="password_confirmation">Confirm Password</Form.Label>
                    <Form.Control
                        className={errors.password_confirmation ? 'is-invalid' : ''}
                        id="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        type="password"
                        autoComplete="new-password"
                    />
                    {errors.password_confirmation && <p className="text-danger">{errors.password_confirmation}</p>}
                </Form.Group>
                <div className="d-flex align-items-center gap-4">
                    <Button variant="primary" type="submit" disabled={processing}>
                        Save
                    </Button>
                    {recentlySuccessful && <p className="text-sm text-gray-600">Saved.</p>}
                </div>
            </Form>
        </section>
    );
}
