/**
 * CodeX: Breaking Down the Barriers to Source Code Sharing
 *
 * Copyright (c) Xerox Corporation, CodeX, 2007. All Rights Reserved
 *
 * This file is licensed under the CodeX Component Software License
 *
 * @author Anne Hardyau
 * @author Marc Nazarian
 */

package com.xerox.xrce.codex.jri.model.tracker;

import java.rmi.RemoteException;
import java.util.ArrayList;
import java.util.List;
import java.util.StringTokenizer;

import org.apache.axis.AxisFault;

import com.xerox.xrce.codex.jri.exceptions.CxException;
import com.xerox.xrce.codex.jri.exceptions.CxRemoteException;
import com.xerox.xrce.codex.jri.exceptions.CxServerException;
import com.xerox.xrce.codex.jri.messages.JRIMessages;
import com.xerox.xrce.codex.jri.model.CxFromServer;
import com.xerox.xrce.codex.jri.model.CxServer;
import com.xerox.xrce.codex.jri.model.wsproxy.Artifact;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactCC;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactDependency;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactFieldNameValue;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactFieldValue;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactFile;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactFollowup;
import com.xerox.xrce.codex.jri.model.wsproxy.ArtifactHistory;
import com.xerox.xrce.codex.jri.utils.CodexDate;

/**
 * CxArtifact is the class for Artefact. Artifact is the generic name for Object
 * tracked by the CodeX trackers. An artifact have some predefined fields (id,
 * summary, severity, etc.) and some extra fields defined by the user. An
 * artifact belongs to a tracker, and a tracker can have several artifacts.
 * 
 */
public class CxArtifact extends CxFromServer {

    /**
     * The tracker this artifact belong to
     */
    CxTracker tracker;

    /**
     * The Artifact Object (stub generated by WSDL2JAVA)
     */
    Artifact artifact;

    /**
     * The follow-up comments of this artifact
     */
    List<CxArtifactFollowUp> followUps;

    /**
     * The attached files of this artifact
     */
    List<CxArtifactAttachedFile> attachedFiles;

    /**
     * The CC List of this artifact
     */
    List<CxArtifactCC> ccList;

    /**
     * The artifact dependencies of this artifact
     */
    List<CxArtifactDependency> dependencies;

    /**
     * The artifact inverse dependencies of this artifact
     */
    List<CxArtifactDependency> inverseDependencies;

    /**
     * The history of this artifact
     */
    List<CxArtifactHistory> history;

    /**
     * Constructor from an Artifact Object
     * 
     * @param server the server this artifact belongs to
     * @param artifact the artifact object
     */
    public CxArtifact(CxServer server, Artifact artifact) {
        super(server);
        this.artifact = artifact;
    }

    /**
     * Returns the artifact object (generated by WSDL2JAVA)
     * 
     * @return the artifact Object
     */
    public Artifact getArtifact() {
        return artifact;
    }

    /**
     * Returns the tracker this artifact belongs to
     * 
     * @return the tracker this artifact belongs to
     */
    public CxTracker getTracker() {
        return tracker;
    }

    /**
     * Sets the tracker to this artifact
     * 
     * @param tracker the tracker this artifact belongs to
     */
    public void setTracker(CxTracker tracker) {
        this.tracker = tracker;
    }

    /**
     * Returns the artifact dependencies of this artifact
     * 
     * @return the artifact dependencies of this artifact
     */
    public List<CxArtifactDependency> getDependencies() {
        return dependencies;
    }

    /**
     * Sets the artifact dependencies to this artifact
     * 
     * @param dependencies the artifact dependencies to set to this artifact
     */
    public void setDependencies(List<CxArtifactDependency> dependencies) {
        this.dependencies = dependencies;
    }

    /**
     * Returns the inverse dependencies of this artifact
     * 
     * @return the inverse dependencies of this artifact
     */
    public List<CxArtifactDependency> getInverseDependencies() {
        return inverseDependencies;
    }

    /**
     * Sets the inverse dependencies to this artifact
     * 
     * @param inverseDependencies the inverse dependencies to set to this
     *        artifact
     */
    public void setInverseDependencies(
                                       List<CxArtifactDependency> inverseDependencies) {
        this.inverseDependencies = inverseDependencies;
    }

    /**
     * Returns the follow-up comments of this artifact
     * 
     * @return the list of the follow-up comments of this artifact
     */
    public List<CxArtifactFollowUp> getFollowUps() {
        return followUps;
    }

    /**
     * Sets the follow-up comments to this artifact
     * 
     * @param followUps the follow-up comments to set to this artifact
     */
    public void setFollowUps(List<CxArtifactFollowUp> followUps) {
        this.followUps = followUps;
    }

    /**
     * Returns the attached files of this artifact
     * 
     * @return the list of attached files of this artifact
     */
    public List<CxArtifactAttachedFile> getAttachedFiles() {
        return attachedFiles;
    }

    /**
     * Sets the attached files to this artifact
     * 
     * @param attachedFiles the attached files to set to this artifact
     */
    public void setAttachedFile(List<CxArtifactAttachedFile> attachedFiles) {
        this.attachedFiles = attachedFiles;
    }

    /**
     * Returns the history of this artifact
     * 
     * @return the list of itory of this artifact
     */
    public List<CxArtifactHistory> getHistory() {
        return history;
    }

    /**
     * Sets the history to this artifact
     * 
     * @param history the history to set to this artifact
     */
    public void setHistory(List<CxArtifactHistory> history) {
        this.history = history;
    }

    /**
     * Returns the CC List of this artifact
     * 
     * @return the list of CC of this artifact
     */
    public List<CxArtifactCC> getCcList() {
        return ccList;
    }

    /**
     * Sets the CCList to this artifact
     * 
     * @param ccList the list of CC to set to this artifact
     */
    public void setCcList(List<CxArtifactCC> ccList) {
        this.ccList = ccList;
    }

    /**
     * Returns the list of ExtraFields of this artifact
     * 
     * @return the list of ExtraFields of this artifact
     */
    private List<CxArtifactFieldValue> getExtraFields() {
        ArtifactFieldValue[] fieldValues = artifact.getExtra_fields();
        List<CxArtifactFieldValue> artifactFieldValues = new ArrayList<CxArtifactFieldValue>();
        for (ArtifactFieldValue fieldValue : fieldValues) {
            artifactFieldValues.add(new CxArtifactFieldValue(this.getServer(), fieldValue));
        }
        return artifactFieldValues;
    }

    /**
     * Returns the ID of this artifact
     * 
     * @return the ID of this artifact
     */
    public int getId() {
        return artifact.getArtifact_id();
    }

    /**
     * Returns the status ID of this artifact
     * 
     * @return the status Id of this artifact
     */
    public int getStatusID() {
        return artifact.getStatus_id();
    }

    /**
     * Returns the close date of this artifact
     * 
     * @return the close date of this artifact
     */
    public int getCloseDate() {
        return artifact.getClose_date();
    }

    /**
     * Returns the summary of this artifact
     * 
     * @return the summary of this artifact
     */
    public String getSummary() {
        return artifact.getSummary();
    }

    /**
     * Returns the details (the original submission) of this artifact
     * 
     * @return the details (the original submission) of this artifact
     */
    public String getDetails() {
        return artifact.getDetails();
    }

    /**
     * Returns the severity of this artifact
     * 
     * @return the severity of this artifact
     */
    public int getSeverity() {
        return artifact.getSeverity();
    }

    /**
     * Returns the ID of the user that submitted this artifact
     * 
     * @return the ID of the user that submitted this artifact
     */
    private int getSubmittedBy() {
        return artifact.getSubmitted_by();
    }

    /**
     * Returns the value (textual data for display) of the field in the report
     * <code>reportID</code>, field in the column <code>i</code> for this
     * artifact.
     * 
     * @param reportId ID of the selected report
     * @param i index of the column in the report (first column is 0)
     * @return the value of the field (with index i)
     * @throws CxException
     */
    public String getFieldValue(int reportId, int i) throws CxException {
        CxArtifactReport report = null;
        List<CxArtifactReport> reports = tracker.getReports(false);
        for (CxArtifactReport currentReport : reports) {
            if (currentReport.getID() == reportId) {
                report = currentReport;
            }
        }
        if (report == null) {
            report = reports.get(0);
        }

        // ArtifactReport report = tracker.getReports().get(0);
        List<CxArtifactReportField> reportFieldsResult = new ArrayList<CxArtifactReportField>();
        List<CxArtifactReportField> reportFields = report.getFields();
        for (CxArtifactReportField reportField : reportFields) {
            if (reportField.isShownOnResult()) {
                reportFieldsResult.add(reportField);
            }
        }

        CxArtifactReportField reportField = reportFieldsResult.get(i);
        String fieldName = reportField.getName();
        CxArtifactField field = tracker.getField(fieldName);
        return this.getFieldValue(field);
    }

    /**
     * Returns the value (textual data for display) of the field in the current
     * selected report, field in the column <code>i</code> for this artifact
     * 
     * @param i index of the column in the report (first column is 0)
     * @return the value of the field with index i
     * @throws CxException
     */
    public String getFieldValue(int i) throws CxException {
        return this.getFieldValue(this.tracker.getReportIdSelected(), i);
    }

    /**
     * Returns the value of the field <code>field</code> for this artifact.
     * 
     * @param field the field we want to retrieve the value
     * @return the value of the field
     * @throws CxException
     */
    public String getFieldValue(CxArtifactField field) throws CxException {
        if (field.isStandard()) {
            return this.getStandardFieldValue(field.getName());
        } else {
            return this.getExtraFieldValue(field);
        }
    }

    /**
     * Returns the value of the field <code>field</code> for this artifact. In
     * this method, we assume that field is an extra field.
     * 
     * @param field the extra field we want to retrieve the value for this
     *        artifact
     * @return the value of the field
     */
    private String getExtraFieldValue(CxArtifactField field) {
        if (field.getDisplayType().equals("SB")) { // Single Box //$NON-NLS-1$
            List<CxArtifactFieldValue> artifactFieldValues = this.getExtraFields();
            for (CxArtifactFieldValue artifactFieldValue : artifactFieldValues) {
                if (artifactFieldValue.getFieldID() == field.getID()) {
                    int fieldValue = new Integer(artifactFieldValue.getFieldValue());
                    List<CxArtifactFieldValueList> values = field.getAvailableValues();
                    for (CxArtifactFieldValueList value : values) {
                        if (value.getID() == fieldValue) {
                            return value.getValue();
                        }
                    }
                    return JRIMessages.getString("CxArtifact.unknow_value"); //$NON-NLS-1$
                }
            }
        } else if (field.getDisplayType().equals("MB")) { // Multi Box
            // //$NON-NLS-1$
            String returnedValues = ""; //$NON-NLS-1$
            List<CxArtifactFieldValue> artifactFieldValues = this.getExtraFields();
            for (CxArtifactFieldValue artifactFieldValue : artifactFieldValues) {
                if (artifactFieldValue.getFieldID() == field.getID()) {
                    List<Integer> fieldValues = getValuesList(
                        artifactFieldValue.getFieldValue(), ",");
                    List<CxArtifactFieldValueList> values = field.getAvailableValues();
                    for (int fieldValue : fieldValues) {
                        for (CxArtifactFieldValueList value : values) {
                            if (value.getID() == fieldValue) {
                                returnedValues += value.getValue() + ",";
                            }
                        }
                    }
                    // remove the last comma
                    if (returnedValues.length() > 0)
                        returnedValues = returnedValues.substring(0,
                            returnedValues.length() - 1);
                }
            }
            return returnedValues;
        } else if (field.getDisplayType().equals("TF")) { // Text Field
            // //$NON-NLS-1$
            List<CxArtifactFieldValue> artifactFieldValues = this.getExtraFields();
            for (CxArtifactFieldValue artifactFieldValue : artifactFieldValues) {
                if (artifactFieldValue.getFieldID() == field.getID()) {
                    return artifactFieldValue.getFieldValue();
                }
            }
        } else if (field.getDisplayType().equals("DF")) { // Date Field
            // //$NON-NLS-1$
            List<CxArtifactFieldValue> artifactFieldValues = this.getExtraFields();
            for (CxArtifactFieldValue artifactFieldValue : artifactFieldValues) {
                if (artifactFieldValue.getFieldID() == field.getID()) {
                    return CodexDate.getFormattedDate(
                        new Integer(artifactFieldValue.getFieldValue()),
                        JRIMessages.getString("CxArtifact.format_day"));//$NON-NLS-1$
                }
            }
        } else if (field.getDisplayType().equals("TA")) { // Text Area
            // //$NON-NLS-1$
            List<CxArtifactFieldValue> artifactFieldValues = this.getExtraFields();
            for (CxArtifactFieldValue artifactFieldValue : artifactFieldValues) {
                if (artifactFieldValue.getFieldID() == field.getID()) {
                    return artifactFieldValue.getFieldValue();
                }
            }
        }

        return null;
    }

    private List<Integer> getValuesList(String valueString, String separator)
                                                                             throws NumberFormatException {
        List<Integer> valuesList = new ArrayList<Integer>();
        StringTokenizer stringToken = new StringTokenizer(valueString, separator
                                                                       + " ");
        while (stringToken.hasMoreElements()) {
            String element = (String) stringToken.nextElement();
            valuesList.add(new Integer(element));
        }
        return valuesList;
    }

    /**
     * Returns the value of the field named <code>fieldName</code> for this
     * artifact. In this method, we assume that fieldName is a standard field.
     * Standard fields are:
     * <ul>
     * <li>artifact_id</li>
     * <li>status_id</li>
     * <li>submitted_by</li>
     * <li>open_date</li>
     * <li>close_date</li>
     * <li>summary</li>
     * <li>details</li>
     * <li>severity</li>
     * </ul>
     * 
     * @param fieldName the name of the field we want to retrieve the value
     * @return the value of the field, or "" if the field is not found or is not
     *         a standard one.
     * @throws CxException
     */
    private String getStandardFieldValue(String fieldName) throws CxException {
        if (fieldName.equals("artifact_id")) { //$NON-NLS-1$
            return new Integer(artifact.getArtifact_id()).toString();
        } else if (fieldName.equals("status_id")) { //$NON-NLS-1$
            CxArtifactField statusField = tracker.getField("status_id"); //$NON-NLS-1$
            List<CxArtifactFieldValueList> values = statusField.getAvailableValues();
            for (CxArtifactFieldValueList value : values) {
                if (value.getID() == this.getStatusID()) {
                    return value.getValue();
                }
            }
        } else if (fieldName.equals("submitted_by")) { //$NON-NLS-1$
            CxArtifactField submittedByField = tracker.getField("submitted_by"); //$NON-NLS-1$
            List<CxArtifactFieldValueList> values = submittedByField.getAvailableValues();
            for (CxArtifactFieldValueList value : values) {
                if (value.getID() == this.getSubmittedBy()) {
                    return value.getValue();
                }
            }
        } else if (fieldName.equals("open_date")) { //$NON-NLS-1$
            return CodexDate.getFormattedDate(artifact.getOpen_date(),
                JRIMessages.getString("CxArtifact.format_hour"));//$NON-NLS-1$
        } else if (fieldName.equals("close_date")) { //$NON-NLS-1$
            return CodexDate.getFormattedDate(artifact.getClose_date(),
                JRIMessages.getString("CxArtifact.format_day"));//$NON-NLS-1$
        } else if (fieldName.equals("summary")) { //$NON-NLS-1$
            return this.getSummary();
        } else if (fieldName.equals("details")) { //$NON-NLS-1$
            return this.getDetails();
        } else if (fieldName.equals("severity")) { //$NON-NLS-1$
            CxArtifactField severityField = tracker.getField("severity"); //$NON-NLS-1$
            List<CxArtifactFieldValueList> values = severityField.getAvailableValues();
            for (CxArtifactFieldValueList value : values) {
                if (value.getID() == this.getSeverity()) {
                    return value.getValue();
                }
            }
        }
        return ""; //$NON-NLS-1$
    }

    /**
     * Init the artifact. This method sets:
     * <ul>
     * <li>the follow-up comments</li>
     * <li>the attached files</li>
     * <li>the CC List</li>
     * <li>the dependencies</li>
     * <li>the inverse dependencies</li>
     * <li>the history</li>
     * </ul>
     * of this artifact.
     * 
     * @throws CxException
     */
    public void initArtifact() throws CxException {
        try {
            // Get the follow-up comments
            if (this.followUps == null) {
                this.followUps = new ArrayList<CxArtifactFollowUp>();
                ArtifactFollowup[] artFollowups = null;
                artFollowups = server.getBinding().getArtifactFollowups(
                    server.getSession().getSession_hash(),
                    this.tracker.getGroup().getId(),
                    this.tracker.getGroupArtifactID(), this.getId());

                for (ArtifactFollowup artFollowup : artFollowups) {
                    followUps.add(new CxArtifactFollowUp(this.getServer(), artFollowup));
                }
            }

            // Get the attached files (not the content, just the description)
            if (this.attachedFiles == null) {
                this.attachedFiles = new ArrayList<CxArtifactAttachedFile>();
                ArtifactFile[] artFiles = null;
                artFiles = server.getBinding().getArtifactAttachedFiles(
                    server.getSession().getSession_hash(),
                    this.tracker.getGroup().getId(),
                    this.tracker.getGroupArtifactID(), this.getId());

                for (ArtifactFile artFile : artFiles) {
                    CxArtifactAttachedFile artAttFile = new CxArtifactAttachedFile(this.getServer(), artFile);
                    artAttFile.setArtifact(this);
                    this.attachedFiles.add(artAttFile);
                }
            }

            // Get the cc list
            if (this.ccList == null) {
                this.ccList = new ArrayList<CxArtifactCC>();
                ArtifactCC[] artCCArray = null;

                artCCArray = server.getBinding().getArtifactCCList(
                    server.getSession().getSession_hash(),
                    this.tracker.getGroup().getId(),
                    this.tracker.getGroupArtifactID(), this.getId());

                for (ArtifactCC artCC : artCCArray) {
                    ccList.add(new CxArtifactCC(this.getServer(), artCC));
                }
            }

            // Get the dependencies
            if (this.dependencies == null) {
                this.dependencies = new ArrayList<CxArtifactDependency>();
                ArtifactDependency[] artDependencies = null;

                artDependencies = server.getBinding().getArtifactDependencies(
                    server.getSession().getSession_hash(),
                    this.tracker.getGroup().getId(),
                    this.tracker.getGroupArtifactID(), this.getId());

                for (ArtifactDependency artDependency : artDependencies) {
                    dependencies.add(new CxArtifactDependency(this.getServer(), artDependency));
                }
            }

            // Get the inverse dependencies
            if (this.inverseDependencies == null) {
                this.inverseDependencies = new ArrayList<CxArtifactDependency>();
                ArtifactDependency[] artInverseDependencies = null;

                artInverseDependencies = server.getBinding().getArtifactInverseDependencies(
                    server.getSession().getSession_hash(),
                    this.tracker.getGroup().getId(),
                    this.tracker.getGroupArtifactID(), this.getId());

                for (ArtifactDependency artInverseDependency : artInverseDependencies) {
                    inverseDependencies.add(new CxArtifactDependency(this.getServer(), artInverseDependency));
                }
            }

            // Get the history
            if (this.history == null) {
                this.history = new ArrayList<CxArtifactHistory>();
                ArtifactHistory[] artHistory = null;

                artHistory = server.getBinding().getArtifactHistory(
                    server.getSession().getSession_hash(),
                    this.tracker.getGroup().getId(),
                    this.tracker.getGroupArtifactID(), this.getId());

                for (ArtifactHistory currentArtHistory : artHistory) {
                    history.add(new CxArtifactHistory(this.getServer(), currentArtHistory));
                }
            }

        } catch (AxisFault axisFault) {
            emptyUnusedInfo();
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            emptyUnusedInfo();
            throw new CxRemoteException(re);
        }

    }

    /**
     * Reset the attributes of this artifact. This method resets:
     * <ul>
     * <li>the follow-up comments</li>
     * <li>the attached files</li>
     * <li>the CC List</li>
     * <li>the dependencies</li>
     * <li>the inverse dependencies</li>
     * <li>the history</li>
     * </ul>
     */
    public void emptyUnusedInfo() {
        this.followUps = null;
        this.attachedFiles = null;
        this.ccList = null;
        this.dependencies = null;
        this.inverseDependencies = null;
        this.history = null;
    }

    /**
     * Update this artifact with the given values.
     * 
     * @param statusId the new status ID
     * @param closeDate the new close date
     * @param summary the new summary
     * @param details the new details
     * @param severity the new severity
     * @param fields the new extra fields values
     * @return the ID of this artifact, or -1 if the update failed.
     * @throws CxException
     */
    public int updateArtifact(int statusId, int closeDate, String summary,
                              String details, int severity,
                              List<CxArtifactFieldNameValue> fields)
                                                                    throws CxException {
        int retval = -1;
        try {
            ArtifactFieldNameValue[] fieldsArray = new ArtifactFieldNameValue[fields.size()];
            int i = 0;
            for (CxArtifactFieldNameValue field : fields) {
                fieldsArray[i] = new ArtifactFieldNameValue(field.getFieldName(), field.getArtifactId(), field.getFieldValue());
                i++;
            }
            retval = server.getBinding().updateArtifactWithFieldNames(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(), statusId,
                closeDate, summary, details, severity, fieldsArray, null, 0);
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
        return retval;
    }

    /**
     * Add a follow-up comment to this artifact
     * 
     * @param comment the message to adds
     * @param commentType the ID of the comment type if needed, or 100 if not
     *        used
     * @return true if the follow-up has been added, false otherwise
     * @throws CxException
     */
    public boolean addFollowUpcomment(String comment, int commentType)
                                                                      throws CxException { // TODO
        // review
        boolean ok = false;
        try {
            ok = server.getBinding().addArtifactFollowup(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(), comment,
                commentType);
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
        return ok;
    }

    /**
     * Add an attached file to this artifact
     * 
     * @param encodedFileData the content of the file encoded in Base64
     * @param description the description of the file
     * @param filename the name of the file (the end name, no path)
     * @param filetype the mime-type of the file
     * @return the ID of the attached file, or -1 if the attachement failed.
     * @throws CxException
     */
    public int addAttachedFile(String encodedFileData, String description,
                               String filename, String filetype)
                                                                throws CxException {
        int retval = -1;
        try {
            retval = server.getBinding().addArtifactAttachedFile(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(),
                encodedFileData, description, filename, filetype);
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
        return retval;
    }

    /**
     * Delete an attached file to this artifact
     * 
     * @param attachedFileID the ID of the attached file to delete
     * @return -1 if the deletion failed
     * @throws CxException
     */
    public int deleteAttachedFile(int attachedFileID) throws CxException {
        int retval = -1;
        try {
            retval = server.getBinding().deleteArtifactAttachedFile(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(), attachedFileID);
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
        return retval;
    }

    /**
     * Add a CCList to this artifact
     * 
     * @param ccList the CCList (CodeX username or email adresses separated by a
     *        comma)
     * @param comment the comment associated to the CCLists
     * @throws CxException
     */
    public void addArtifactCC(String ccList, String comment) throws CxException {
        try {
            server.getBinding().addArtifactCC(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(), ccList,
                comment);
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
    }

    /**
     * Delete a CC of this artifact
     * 
     * @param artcc the CC to delete
     * @throws CxException
     */
    public void deleteArtifactCC(CxArtifactCC artcc) throws CxException {
        try {
            server.getBinding().deleteArtifactCC(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(), artcc.getId());
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
    }

    /**
     * Add an artifact dependency (or alist of artifact dependencies) to this
     * artifact
     * 
     * @param artDepIds the IDs of the dependencies (separated by a comma)
     * @throws CxException
     */
    public void addArtifactDependency(String artDepIds) throws CxException {
        try {
            server.getBinding().addArtifactDependencies(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(), artDepIds);
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
    }

    /**
     * Delete an artifact dependency
     * 
     * @param artDep the dependency to delete
     * @return -1 if the deletion failed.
     * @throws CxException
     */
    public int deleteArtifactDependency(CxArtifactDependency artDep)
                                                                    throws CxException {
        int retVal = -1;
        try {
            retVal = server.getBinding().deleteArtifactDependency(
                server.getSession().getSession_hash(),
                this.tracker.getGroup().getId(),
                this.tracker.getGroupArtifactID(), this.getId(),
                artDep.getIsDependentOnArtifactID());
        } catch (AxisFault axisFault) {
            throw new CxServerException(axisFault);
        } catch (RemoteException re) {
            throw new CxRemoteException(re);
        }
        return retVal;
    }

}
