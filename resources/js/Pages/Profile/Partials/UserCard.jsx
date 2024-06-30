import CardContainer from '@/Components/CardContainer.jsx';
import { Button } from 'react-bootstrap';

export default function UserCard({ auth, user }) {
  return (
    <CardContainer header={user.name}>
      <p className="card-text">
        {user.items && (
          <small className="text-muted">
            {user.items.length ? `${user.items.length} item${user.items.length > 1 ? 's' : ''}` : 'No items listed'}
            {user.location && <> · Location: {user.location}</>}
            {user.email && <> · Email: <a href={'mailto:' + user.email}>{user.email}</a></>}
            {user.phone_number && <> · Phone: <a href={'tel:' + user.phone_number}>{user.phone_number}</a></>}
          </small>
        )}
      </p>

      {user.bio && <p className="card-text">{user.bio}</p>}

      {auth?.user?.id === user.id && (
        <div className="mt-2">
          <Button href={route('profile.edit')} variant="primary">
            Edit
          </Button>
        </div>
      )}
    </CardContainer>
  );
}
