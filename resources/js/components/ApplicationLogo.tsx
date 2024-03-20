import { StyleHTMLAttributes } from 'react';
import logo from '@/assets/images/lider-sandbox-1.png';

export default function ApplicationLogo(props: StyleHTMLAttributes<HTMLImageElement>) {
    return (
        <img src={logo} style={{ height: 64, widows: 64, ...props, borderRadius: '50%' }} />
    );
}
