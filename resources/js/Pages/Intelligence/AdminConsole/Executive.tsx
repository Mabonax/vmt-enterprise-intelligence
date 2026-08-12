import ConsolePage from './ConsolePage';

export default function Executive(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="overview" />;
}
