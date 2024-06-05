import { StyleHTMLAttributes } from 'react';
import logo from '@/assets/images/lider-sandbox-1.png';

export default function ApplicationLogo(props: StyleHTMLAttributes<HTMLImageElement>) {
    const finalHeight = props.style?.height || 100;
    const finalWidth = props.style?.width || 100;

    return (
        <img src={logo} style={{ height: finalHeight, width: finalWidth, ...props }} />
    );
}
