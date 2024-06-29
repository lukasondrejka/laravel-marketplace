import React from 'react';
import {Pagination as ReactPagination} from 'react-bootstrap';
import {Link} from "@inertiajs/react";

export default function Pagination({ links }) {
    return (
        <ReactPagination className="justify-content-center">
            {links.map((link, index) => (
                <ReactPagination.Item key={index}  href={link.url} active={link.active} as={Link}>
                    {link.label}
                </ReactPagination.Item>
            ))}
        </ReactPagination>
    );
};
