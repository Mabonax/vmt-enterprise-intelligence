import ConsolePage from './ConsolePage';

export default function Audit(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="audit" />;
}
