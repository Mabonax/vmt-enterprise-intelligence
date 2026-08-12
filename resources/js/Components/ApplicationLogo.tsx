import ApplicationMark from '@/Components/Vip/ApplicationMark';

type ApplicationLogoProps = {
    className?: string;
};

export default function ApplicationLogo({
    className = '',
}: ApplicationLogoProps) {
    return <ApplicationMark className={className} />;
}
