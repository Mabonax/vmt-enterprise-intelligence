type ApplicationMarkProps = {
    className?: string;
};

export default function ApplicationMark({
    className = '',
}: ApplicationMarkProps) {
    return (
        <div className={`vip-mark ${className}`.trim()}>
            <span className="vip-mark__frame" />
            <div>
                <p className="vip-mark__eyebrow">VMT</p>
                <p className="vip-mark__title">Enterprise AI Gateway</p>
            </div>
        </div>
    );
}
