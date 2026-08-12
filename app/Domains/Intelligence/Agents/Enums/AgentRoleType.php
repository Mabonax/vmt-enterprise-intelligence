<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Enums;

enum AgentRoleType: string
{
    case ResearchAgent = 'research_agent';
    case ArchitectAgent = 'architect_agent';
    case PlanningAgent = 'planning_agent';
    case DeveloperAgent = 'developer_agent';
    case ReviewerAgent = 'reviewer_agent';
    case QaAgent = 'qa_agent';
    case SecurityAgent = 'security_agent';
    case KnowledgeAgent = 'knowledge_agent';
    case DocumentationAgent = 'documentation_agent';
    case ReportingAgent = 'reporting_agent';
    case DataAnalyst = 'data_analyst';
    case WorkflowAgent = 'workflow_agent';
    case ToolAgent = 'tool_agent';
    case CommunicationAgent = 'communication_agent';
    case ComplianceAgent = 'compliance_agent';
    case ExecutiveAdvisor = 'executive_advisor';
}
